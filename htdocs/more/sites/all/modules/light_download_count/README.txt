 This is a very light download counter. It works for private downloads and can also count
 public downloads if the following lines are added in the Rewrite section of the site's
 .htaccess file:
 
 RewriteCond %{QUERY_STRING} =""
 RewriteCond %{REQUEST_URI} ^/files/
 RewriteRule ^(.*)$ index.php?q=download_count&file=$1 [L,QSA]
 
 The download counter value for each file is saved in the files table along with the file's
 information. There is no user interface. If you want to display the download counter value
 you'll have to update your themeing functions and include the download field of the file
 array. For example, for the filefield module, this will be something like this:
 
 function phptemplate_filefield($file) {
  $output = '';
  if (user_access('view filefield uploads') && is_file($file['filepath']) && $file['list']) {
    $path = ($file['fid'] == 'upload')
            ? file_create_filename($file['filename'], file_create_path($field['widget']['file_path']))
            : $file['filepath'];
    $output = '<div class="filefield-item">';
    $output .= theme('filefield_icon', $file);
    $output .= l($file['description'], file_create_url($path));
    // include the download counter value
    if(isset($file['downloads'])) {
      $output .= ' (' . format_plural($file['downloads'], '@count download', '@count downloads') . ')';
    }
    $output .= '</div>';
  }
  return $output;
}  

