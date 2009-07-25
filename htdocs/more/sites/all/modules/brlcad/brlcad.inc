<?php //$Id$

/**
 * @file
 * BRLCAD related methods.
 */

/**
 * Runs the BRLCAD metadata extraction and raytracing script, parses and returns the results. 
 * @param $model filepath of the model file
 * @param $objects array of object names to raytrace
 * @return array metadata fields and raytrace image paths 
 */
function brlcad_metadata_and_rt($model, $objects = null) {
  // obtain model file
  if(empty($model)) {
    return;
  }
  
  // prepare environment variables
  $size = (int)variable_get('brlcad_raytrace_size', 512);
  $views = str_replace(array("\r\n", "\n"), ':', variable_get('brlcad_raytrace_ae', ''));
  $image = tempnam(realpath(file_directory_temp()), 'rtimg_');
  if(is_array($objects)) {
    $objects = implode(' ', $objects);
  }
  if(empty($objects)) {
    $objects = '*';
  }
  $env = array(
    'image' => $image,
    'size' => $size,
    'views' => $views,
    'objects' => $objects, 
  );

  // run the script
  $script = drupal_get_path('module', 'brlcad') . '/scripts/' . variable_get('brlcad_version', 'generic') . '.txt';
  $output = brlcad_run_mged_script($script, $model, $env);
  unlink($image);
  if(empty($output) ) {
    return;
  }
  
  // parse the output
  preg_match_all('|DATABASE:([^\n]+)\n(.+)\nDATABASE:\1|sm', $output, $fields);
  $fields = array_combine($fields[1], $fields[2]);
  $required_fields = array('TITLE', 'VERSION', 'SUMMARY', 'UNITS', 'OBJECTS');
  foreach($required_fields as $field) {
    if(!isset($fields[$field])) {
      $fields[$field] = null;
    }
  }

  // convert object listing to a CSV list
  preg_match_all('|^(.+?) +(\w+) +(\d+) +(\d+) +(\d+)$|m', $fields['OBJECTS'], $matches, PREG_SET_ORDER);
  $fields['OBJECTS'] = '';
  foreach($matches as $match) {
    if($fields['OBJECTS']) {
      $fields['OBJECTS'] .= "\n";
    }
    $fields['OBJECTS'] .= $match[1] . ';' . $match[2] . ';' . $match[3] . ';' . $match[4]. ';' . $match[5];
  }
  
  // include image paths
  $fields['IMAGES'] = array();
  $views = explode(':', $views);
  foreach($views as $key => $view) {
    $pixfile = $image . $key . '.pix';
    if(file_exists($pixfile)) {
      $fields['IMAGES'][$view] = $pixfile;
    }
  }
  return $fields;
}

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
    return;
  }
  // prepare the command file
  $command = $matches[1];
  if($command[0] != '/') {
    $command = variable_get('brlcad_bin_path', '/usr/brlcad/bin') . '/' . $command; 
  }
  $command = escapeshellcmd($command);
  
  // prepare arguments
  foreach($params as $key => $param) {
    $params[$key] = escapeshellarg($param);
  }
  
  // prepare full command line
  $command = $command . vsprintf($matches[2], $params);

  $descriptorspec = array(
    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
  );
  $process = proc_open($command, $descriptorspec, $pipes, $cwd, $env);
  if (!is_resource($process)) {
    watchdog('brlcad', t('Failed to execute command %cmd.', array('%cmd' => $command)), WATCHDOG_ERROR);
    return;
  }
  $output = stream_get_contents($pipes[1]);
  fclose($pipes[1]);

  // It is important that you close any pipes before calling proc_close in order to avoid a deadlock
  $return_value = proc_close($process);
  if($return_value) {
    watchdog('brlcad', t('Execution of command %cmd ended with error code @code. The output was: <pre>@output</pre>', array('%cmd' => $command, '@code' => $return_value, '@output' => $output)), WATCHDOG_ERROR);
    return;
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
    return;
  }
  $command = 'mged -cr %s 2>&1 < %s'; 
  $params = array($model, $script);

  $output = brlcad_run_command($command, $params, $env, $cwd);
  if(is_null($output)) {
    return;
  }
  
  // trim leading warning Opened in READ ONLY mode
  if(substr($output, 0, 24) == 'Opened in READ ONLY mode') {
    $output = substr($output, 25);
  }
  return $output;
}

/**
 * Create a model file to the specified format.
 * @param $input_file array with file properties (filename, filepath)
 * @param $output_format name of the output format
 * @param $objects objects to convert (only if the input is brlcad)
 * @return array array with file properties (filename, filepath, description, filemime, filesize, fid = upload)
 */
function brlcad_convert_file($input_file, $output_format, $objects = null) {
  // if input file exist, don't try to convert
  if(empty($input_file)) {
    return;
  }
  // prepare the input file array
  if(is_string($input_file)) {
    $input_file['filepath'] = $input_file;
  }
  $input_file['filename'] = empty($input_file['filename']) ? basename($input_file['filepath']) : $input_file['filename']; 
  
  // find the input file information
  $input_format = brlcad_known_formats(pathinfo($input_file['filename'], PATHINFO_EXTENSION), true);
  
  // if the input and output formats are the same, simply copy one field to the other
  if($input_format == $output_format) {
    $output_file = $input_file;
  } else {
    // prepare the output file
    $output_file = array(
      'filepath' => tempnam(realpath(file_directory_temp()), 'model_'),
      'filename' => brlcad_fix_extension($input_file['filename'], $output_format),
    );
    // convert the file
    if($output_format == 'brlcad') {
      $result = brlcad_convert_other2brlcad($input_format, $input_file['filepath'], $output_file['filepath']);
    } else {
      $result = brlcad_convert_brlcad2other($output_format, $input_file['filepath'], $output_file['filepath'], $objects);
    }

    if(!$result) {
      return;
    }
  }
  if(empty($output_file['filesize'])) {
    $output_file['filesize'] = filesize($output_file['filepath']);  
  }
  if(empty($output_file['filemime'])) {
    $output_file['filemime'] = file_get_mimetype($output_file['filepath']);  
  }
  if(empty($output_file['description'])) {
    $output_file['description'] = $output_file['filename'];
  }
  if(empty($output_file['fid'])) {
    $output_file['fid'] = 'upload';
  }
  return $output_file;
}

/**
 * Proxy function for conversion from g to another format.
 * @param $output_format name of the output format
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2other($output_format, $input, $output, $objects) {
  $function = 'brlcad_convert_brlcad2' . $output_format;
  if(!function_exists($function)) {
    watchdog('brlcad', t('Conversion from BRLCAD g format to %format is not supported.', array('%format' => $output_format)));
    return;
  }
  return $function($input, $output, $objects);
}

/**
 * Proxy function for conversion from another format to g.
 * 
 * @param $input_format name of the input format
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_other2brlcad($input_format, $input, $output) {
  $function = 'brlcad_convert_' . $input_format . '2brlcad';
  if(!function_exists($function)) {
    watchdog('brlcad', t('Conversion from %format format to BRLCAD g format is not supported.', array('%format' => $input_format)));
    return;
  }
  return $function($input, $output);
}

/**
 * Convert g file to asc
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2asc($input, $output) {
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
function brlcad_convert_asc2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2acad($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-acad -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert g file to dxf
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2autocad($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-dxf -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert dxf file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_autocad2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2euclid($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-euclid -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert euclid file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_euclid2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2iges($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-iges -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert iges file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_iges2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2stl($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-stl -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert stl file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_stl2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2tankill($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-tankill -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert tankill file to g
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @return boolean true if conversion succeded
 */
function brlcad_convert_tankill2brlcad($input, $output) {
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
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2vrml($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-vrml -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Convert g file to x3d
 * 
 * @param $input filename of the input file
 * @param $output filename of the output file
 * @param $objects array of object names to convert
 * @return boolean true if conversion succeded
 */
function brlcad_convert_brlcad2x3d($input, $output, $objects) {
  $input = realpath($input);
  if(empty($input) || empty($objects)) {
    return false;
  }
  $command = 'g-x3d -o %s %s' . str_repeat(' %s', count($objects));
  array_unshift($objects, $output, $input);
  $result = brlcad_run_command($command, $objects);
  return !is_null($result); 
}

/**
 * Updates the extension of a file to match its format.
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
 * @param $format name of the format or extension (used for a specific lookup)
 * @param $use_extension true if do an extension lookup, or false to do a format name lookup is performed 
 */
function brlcad_known_formats($format = null, $use_extension = false) {
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
    // if it's an extension lookup, return the associated format
    if($use_extension) {
      return array_search(strtolower($format), $known_formats);
    }
    // if it's a format lookup, return the associated extension
    if(isset($known_formats[$format])) {
      return $known_formats[$format];
    }
    // return false if the format was not found
    return false;
  }
  return $known_formats;
}