<?php //$Id$

/**
 * @file
 * BRLCAD related methods.
 */


/**
 * Runs a BRLCAD specific command and returns the output of stdout. 
 * 
 * @param $command command line using %s as tokens for arguments (and %% for %)
 * @param $params array of command argument strings 
 * @param $env array of command environmental variables
 * @param $cwd string of current working directory
 * @return string output of the command 
 */
function brlcad_run_command($command, $params, $env = null, $cwd = null) {
  if(!preg_match('|^([^ ]+)(.*)$|', $command, $matches)) {
    return null;
  }
  // prepare the command file
  $command = $matches[1];
  if($command[0] != '/') {
    $command = variable_get('brlcad_bin_path', '/usr/brlcad/bin') . '/' . $command; 
  }
  $command = escapeshellcmd($command);
  
  // prepare arguments
  foreach($params as $key => $param) {
    $params[$key] = escapeshellarg($params);
  }
  
  // prepare full command line
  $command = $command . vsprintf($matches[2], $params);
  
  $descriptorspec = array(
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
  );
  $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);
  if (!is_resource($process)) {
    watchdog('brlcad', t('Failed to execute command %cmd.', array('%cmd' => $command)), WATCHDOG_ERROR);
    return null;
  }
  $output = stream_get_contents($pipes[1]);
  fclose($pipes[1]);

  // It is important that you close any pipes before calling proc_close in order to avoid a deadlock
  $return_value = proc_close($process);
  if($return_value) {
    return null;
  }
  return $output;
}

/**
 * Runs a mged script on a given model. If no model is given, simply runs the given script.
 * 
 * @param $script filename of the script to run
 * @param $model filename of the model file (or null)
 * @param $env array of command environmental variables
 * @param $cwd string of current working directory
 * @return string output of the command 
 */
function brlcad_run_mged_script($script, $model = null, $env = null, $cwd = null) {
  $script = realpath($script);
  $model = realpath($model);
  if(empty($script)) {
    return null;
  }
  $command = 'mged -cr %s 2>&1 < %s'; 
  $params = array($model, $script);

  $output = brlcad_run_command($command, $params, $env, $cwd);
  if(is_null($output)) {
    return null;
  }
  
  // trim leading warning Opened in READ ONLY mode
  if(substr($output, 0, 24) == 'Opened in READ ONLY mode') {
    $output = substr($output, 25);
  }
  return $output;
}

/**
 * Convert g file to asc
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2asc($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'g2asc %s %s';
  $params = array($input, $output);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert asc file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_asc2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'asc2g %s %s';
  $params = array($input, $output);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to acad
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2acad($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-acad -o %s %s' . str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to dxf
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2dxf($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-dxf -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert dxf file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_dxf2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'dxf-g %s %s';
  $params = array($input, $output);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to euclid
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2euclid($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-euclid -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert euclid file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_euclid2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'euclid-g -o %s -i %s';
  $params = array($output, $input);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to iges
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2iges($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-iges -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert iges file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_iges2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'iges-g -o %s %s';
  $params = array($output, $input);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to stl
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2stl($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-stl -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert stl file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_stl2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'stl-g %s %s';
  $params = array($input, $output);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}


/**
 * Convert g file to tankill
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2tankill($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-tankill -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert tankill file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_tankill2g($input, $output) {
  $input = realpath($input);
  if(empty($input)) {
    return false;
  }
  $command = 'tankill-g -o %s -i %s';
  $params = array($output, $input);
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}


/**
 * Convert g file to vrml
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2vrml($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-vrml -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Convert g file to x3d
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_g2x3d($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-x3d -o %s %s' + str_repeat(' %s', count($objects));
  $params = array($output, $input) + $objects;
  $result = brlcad_run_command($command, $params);
  return !is_null($result); 
}

/**
 * Updates the extension of a file to matche its format.
 * @param $filename file name 
 * @param $format format 
 * @return string filename of the file with the extension updated
 */
function brlcad_fix_extension($filename, $format) {
  $known_extension = brlcad_known_formats($format);
  if(empty($known_extension)) {
    watchdog('brlcad', t('File format %format is unknown and has no associated extension.', array('%format' => $format)), WATCHDOG_ERROR);
    return $filename;
  }
  // find the current extension
  $extension = pathinfo($filename, PATHINFO_EXTENSION);
  if($extension == $known_extension) {
    return $filename;
  }
  // trim old extension
  if($extension !== '') {
    $filename = substr($filename, 0, -strlen($extension) - 1);
  }
  return $filename . '.' . $known_extension; 
}

/**
 * Returns an array with the know formats and their associated extension.
 */
function brlcad_known_formats($format = null) {
  static $known_formats = array(
    'brlcad' => 'g',
    'ascii' => 'asc',
    'acad' => 'acad',
    'autocad' => 'dxf',
    'euclid' => 'eu',
    'iges' => 'iges',
    'jack' => 'jack',
    'stl' => 'stl',
    'tankill' => 'tkl',
    'vrml' => 'vrml',
    'x3d' => 'x3d', 
  );
  if(!empty($format)) {
    if(empty($known_formats[$format])) {
      return null;
    }
    return $known_formats[$format];
  }
  return $known_formats;
}