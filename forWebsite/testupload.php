<!DOCTYPE HTML>
<html lang="en" class="no-js">
<head>
<meta charset="utf-8">
<title>Expungement Form Generator</title>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/themes/base/jquery-ui.css" id="theme" />
<link rel="stylesheet" href="css/jquery.fileupload-ui.css" />
<link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div id="file_upload">
    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="file[]" multiple>
        <button type="submit">Upload</button>
        <div class="file_upload_label">Upload files</div>
    </form>
    <table class="files">
        <tr class="file_upload_template" style="display:none;">
            <td class="file_upload_preview"></td>
            <td class="file_name"></td>
            <td class="file_size"></td>
            <td class="file_upload_progress"><div></div></td>
            <td class="file_upload_start"><button>Start</button></td>
            <td class="file_upload_cancel"><button>Cancel</button></td>
        </tr>
        <tr class="file_download_template" style="display:none;">
            <td class="file_download_preview"></td>
            <td class="file_name"><a></a></td>
            <td class="file_size"></td>
            <td class="file_download_delete" colspan="3"><button>Delete</button></td>
        </tr>
    </table>
    <div class="file_upload_overall_progress"><div style="display:none;"></div></div>
    <div class="file_upload_buttons">
        <button class="file_upload_start">Start All</button> 
        <button class="file_upload_cancel">Cancel All</button> 
        <button class="file_download_delete">Delete All</button>
    </div>
</div>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8.12/jquery-ui.min.js"></script>
<script src="js/jquery.fileupload.js"></script>
<script src="js/jquery.fileupload-ui.js"></script>
<script src="js/jquery.fileupload-uix.js"></script>
<script src="js/application.js"></script>
</body> 
</html>