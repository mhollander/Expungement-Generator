<html>
<head>
<link href="css/gradeStyle.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/pure/0.6.0/pure-min.css">
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.7/jquery.min.js"> </script>

 <script type="text/javascript">

 $(document).ready(function() {

    $("#section").keydown(function (e) {
        if (e.keyCode == 13){
            $("#display").trigger('click');
        }
    });
    $("#title").keydown(function (e) {
        if (e.keyCode == 13){
            $("#display").trigger('click');
        }
    });

    $("#display").click(function() {

      $.ajax({    //create an ajax request to load_page.php
        type: "GET",
        url: "getGrade.php",
        data: $("#myForm").serialize(),
        dataType: "json",   //expect html to be returned                
        success: function(response){
            // empty the current resulsts, if there are any
            $("#resultsTable > tbody").empty();
            $("#resultsTableSubsection > tbody").empty();
            $("#responsecontainer").show();

            // and then iterate over all of the json objects returned and display
            // update the section table            
            $.each(response.section, function(index, item) {

                var style = item.sealing_exclusion;
                // regardless of the sesaling exclusion, check the grade of the offense
                if ((item.Grade.search("^(M|M3|M2|S)$") == -1))
                    style = 1;
                var extraStyle = "";
                if (style==1)
                    extraStyle = ' class="exclude"';
                else if (style==2)
                    extraStyle = ' class="maybe"';

                var division = item.Section.slice(0,item.Section.length-2);
                var section = item.Section.slice(-2);
                var $tr = $('<tr' + extraStyle + '>').append(    
                $('<td class="codeTitle">').html('<a href="http://www.legis.state.pa.us/cfdocs/legis/LI/consCheck.cfm?txtType=HTM&ttl='+item.Title+'&div=0&chpt='+division+'&sctn='+section+'&subsctn=0" target="_blank">'+item.Title + ' &sect; ' + item.Section+'</a>'),
                $('<td>').text(item.Name),
                $('<td>').text(item.Grade),
                $('<td>').text(item.Number),
                $('<td>').text(item.Total),
                $('<td>').text(item.Percent)).appendTo("#resultsTable");
            });

            // update the subsection table
            $.each(response.subsection, function(index, item) {
                var style = item.sealing_exclusion;
                // regardless of the sesaling exclusion, check the grade of the offense
                if ((item.Grade.search("^(M|M3|M2|S)$") == -1))
                    style = 1;
                var extraStyle = "";
                if (style==1)
                    extraStyle = ' class="exclude"';

                var division = item.Section.slice(0,item.Section.length-2);
                var section = item.Section.slice(-2);
                var $tr = $('<tr' + extraStyle + '>').append(    
                $('<td class="codeTitle">').html('<a href="http://www.legis.state.pa.us/cfdocs/legis/LI/consCheck.cfm?txtType=HTM&ttl='+item.Title+'&div=0&chpt='+division+'&sctn='+section+'&subsctn=0" target="_blank">'+item.Title + " &sect; " + item.Section + " &sect; " + item.Subsection+'</a>'),
                $('<td>').text(item.Name),
                $('<td>').text(item.Grade),
                $('<td>').text(item.Number),
                $('<td>').text(item.Total_W_Subsection),
                $('<td>').text(item.Total_wo_Subsection),
                $('<td>').text(item.Percent_W_Subsection),
                $('<td>').text(item.Percent_wo_Subsection)).appendTo("#resultsTableSubsection");
            });
        } // success

      });// ajax
    }); //click
 }); // ready(function)

</script>
</head>     
<body>
<h3 align="center">Find Offense Grade Estimates</h3>
<form id="myForm">
<div align="center">
<input type="text" name="title" id="title" class="textInput" size=5 /> Pa C.S. &sect; <input type="text" name="section" id="section" size=10 />
<input type="button" id="display" value="Find Crime" style="pure-button pure-button-primary"/>
</div>
<br />
<div id="responsecontainer" align="center" style="display:none">
<div>
<b>Statute Breakdown w/o Subsection</b>
<table id="resultsTable" class="pure-table">
<thead>
<th>Statute</th><th>Common Title</th><th>Grade</th><th>Grade #</th><th>Total Charged</th><th>%</th>
</thead>
<tbody>
</tbody>
</table>
</div>
<br />
<div>
<b>Statute Breakdown w/ Subsections</b>
<table id="resultsTableSubsection" class="pure-table">
<thead>
<th>Statute</th><th>Common Title</th><th>Grade</th><th>Grade #</th><th>Subsection #</th><th>Statute #</th><th>% of Subsection</th><th>% of Statute</th>
</thead>
<tbody>
</tbody>
</table>
</div>
</div>
</body>
</html>