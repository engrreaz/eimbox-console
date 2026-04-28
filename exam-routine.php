<?php
require_once 'header.php';

$slot = $_COOKIE['chain-slot'] ?? null;
$sessionyear = $_COOKIE['chain-session'] ?? null;
$classname = $_COOKIE['chain-class'] ?? null;
$sectionname = $_COOKIE['chain-section'] ?? null;
$exam = $_COOKIE['chain-exam'] ?? null;




?>

<div class="container-xxl flex-grow-1 container-p-y">
  <?php
  $chain_param = '-c 12 -t Choose Values -u -r -b View Routine -h exam';
  include 'components/slot-tree-ui.php';
  ?>


  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">

        </div>
        <div class="card-body">

        </div>
      </div>
    </div>

  </div>

</div>

<?php require_once 'footer.php'; ?>

<!-- ----------------------------------- -->
<script></script>
<!-- ----------------------------------- -->
</body>

</html>




# BEGIN cPanel-generated php ini directives, do not edit
# Manual editing of this file may result in unexpected behavior.
# To make changes to this file, use the cPanel MultiPHP INI Editor (Home >> Software >> MultiPHP INI Editor)
# For more information, read our documentation (https://go.cpanel.net/EA4ModifyINI)
<IfModule php8_module>
  php_flag display_errors Off
  php_value max_execution_time 90
  php_value max_input_time 60
  php_value max_input_vars 1000
  php_value memory_limit 512M
  php_value post_max_size 256M
  php_value session.gc_maxlifetime 1440
  php_value session.save_path "/tmp"
  php_value upload_max_filesize 256M
  php_flag zlib.output_compression Off
</IfModule>
<IfModule lsapi_module>
  php_flag display_errors Off
  php_value max_execution_time 90
  php_value max_input_time 60
  php_value max_input_vars 1000
  php_value memory_limit 512M
  php_value post_max_size 256M
  php_value session.gc_maxlifetime 1440
  php_value session.save_path "/tmp"
  php_value upload_max_filesize 256M
  php_flag zlib.output_compression Off
</IfModule>
# END cPanel-generated php ini directives, do not edit

<IfModule mod_deflate.c>
  # Compress HTML, CSS, JavaScript, Text, XML and fonts
  AddOutputFilterByType DEFLATE application/javascript
  AddOutputFilterByType DEFLATE application/rss+xml
  AddOutputFilterByType DEFLATE application/vnd.ms-fontobject
  AddOutputFilterByType DEFLATE application/x-font
  AddOutputFilterByType DEFLATE application/x-font-opentype
  AddOutputFilterByType DEFLATE application/x-font-otf
  AddOutputFilterByType DEFLATE application/x-font-truetype
  AddOutputFilterByType DEFLATE application/x-font-ttf
  AddOutputFilterByType DEFLATE application/x-javascript
  AddOutputFilterByType DEFLATE application/xhtml+xml
  AddOutputFilterByType DEFLATE application/xml
  AddOutputFilterByType DEFLATE font/opentype
  AddOutputFilterByType DEFLATE font/otf
  AddOutputFilterByType DEFLATE font/ttf
  AddOutputFilterByType DEFLATE image/svg+xml
  AddOutputFilterByType DEFLATE image/x-icon
  AddOutputFilterByType DEFLATE text/css
  AddOutputFilterByType DEFLATE text/html
  AddOutputFilterByType DEFLATE text/javascript
  AddOutputFilterByType DEFLATE text/plain
  AddOutputFilterByType DEFLATE text/xml

  # Remove browser bugs (only needed for really old browsers)
  BrowserMatch ^Mozilla/4 gzip-only-text/html
  BrowserMatch ^Mozilla/4\.0[678] no-gzip
  BrowserMatch \bMSIE !no-gzip !gzip-only-text/html
  Header append Vary User-Agent
</IfModule>

<Files 403.shtml>
  order allow,deny
  allow from all
</Files>


<FilesMatch "\.(jpg|jpeg|png|gif)$">
  Header set Access-Control-Allow-Origin "https://console.eimbox.com"
</FilesMatch>

# php -- BEGIN cPanel-generated handler, do not edit
# Set the “ea-php80” package as the default “PHP” programming language.
<IfModule mime_module>
  AddHandler application/x-httpd-ea-php80___lsphp .php .php8 .phtml
</IfModule>
# php -- END cPanel-generated handler, do not edit

ErrorDocument 404 /eimbox-dashboard/eimbox-materio/pages/404.php
AddType application/json .json