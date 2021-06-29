<?php
function active($current_page){
    $page = $_GET['page'];
    if(isset($page) && $page == $current_page){
        echo 'active'; //this is class name in css
    }
}
?>

<nav class="navbar navbar-dark fixed-top bg-dark flex-md-nowrap p-0 shadow">
	<a class="navbar-brand col-sm-3 col-md-2 mr-0" href="index.php">Hue Logs</a>
</nav>

<nav class="col-md-2 bg-light sidebar">
	<div class="sidebar-sticky">
		<ul class="nav flex-column">
			<li class="nav-item"><a class="nav-link <?php active('lights');?>" href="index.php?page=lights"><span data-feather="sun"></span>Lights</a></li>
			<li class="nav-item"><a class="nav-link <?php active('sensors');?>" href="index.php?page=sensors"><span data-feather="activity"></span>Sensors</a></li>
			<li class="nav-item"><a class="nav-link <?php active('timeline');?>" href="index.php?page=timeline"><span data-feather="clock"></span>Timeline</a></li>
			<!--
			<li class="nav-item"><a class="nav-link <?php active('sensors2');?>" href="index.php?page=sensors2"><span data-feather="activity"></span>Sensors 2</a></li>
			-->
			<li class="nav-item"><a class="nav-link <?php active('light_timeline');?>" href="index.php?page=light_timeline"><span data-feather="clock"></span>Light Timeline</a></li>
			<li class="nav-item"><a class="nav-link <?php active('temp_chart');?>" href="index.php?page=temp_chart"><span data-feather="thermometer"></span>Temp: Chart</a></li>
			<li class="nav-item"><a class="nav-link <?php active('temp_table');?>" href="index.php?page=temp_table"><span data-feather="thermometer"></span>Temp: Table</a></li>
			<li class="nav-item"><a class="nav-link <?php active('temp_first_time_since');?>" href="index.php?page=temp_first_time_since"><span data-feather="thermometer"></span>Temp: Last Time</a></li>
			<li class="nav-item"><a class="nav-link <?php active('lightlevel');?>" href="index.php?page=lightlevel"><span data-feather="sun"></span>Light Level</a></li>
			<li class="nav-item"><a class="nav-link <?php active('presence');?>" href="index.php?page=presence"><span data-feather="users"></span>Motion</a></li>
			<li class="nav-item"><a class="nav-link <?php active('calendar');?>" href="index.php?page=calendar"><span data-feather="calendar"></span>Temperature Calendar</a></li>
			<li class="nav-item"><a class="nav-link <?php active('lights_treemap');?>" href="index.php?page=lights_treemap"><span data-feather="book"></span>Light Treemap</a></li>
		</ul>
	</div>
</nav>
