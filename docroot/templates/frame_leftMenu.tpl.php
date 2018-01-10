<!-- file: frame_leftMenu.tpl -->
<!-- Begin Left Sidebar -->
    <td width="200" height="100%" valign="top" bgcolor="#CCCCCC" background="img/side_back.jpg"> <!--  style="min-height:750px;"> -->
      <div class="sidebar">&nbsp;</div>
      {+if $userId > 0+}

<!--
        <br><br>
        {+if (mj_User::$currUser->isAllowed("HelloHtmlNav"))+}
          <div class="sidebar"><a href="my_application.php?path=ExampleHello.HelloHtml">ExampleHello</a></div>
        {+/if+}
        {+if (mj_User::$currUser->isAllowed("HelloJsonNav"))+}
          <div class="sidebar"><a href="my_application.php?path=ExampleHello.HelloJson">ExampleHello - Json</a></div>
        {+/if+}
--> 
	<div class="sidebar" style="padding-left:0px;"><a href="my_application.php?path=ExampleHello.HelloHtml">HOME</a></div>
	<br>

        {+if (mj_User::$currUser->isAllowed("Queue_CEDR_ItemCreation")) || (mj_User::$currUser->isAllowed("Queue_CEDR_ViewQueues")) ||(mj_User::$currUser->isAllowed("Queue_CEDR_ListEditor")) || (mj_User::$currUser->isAllowed("Reports_CDER_QueueStatus")) || (mj_User::$currUser->isAllowed("Reports_CDER_Productivity")) || (mj_User::$currUser->isAllowed("Reports_CDER_IndividualProd")) || (mj_User::$currUser->isAllowed("Reports_CDER_MonthlySLA"))+}
			<div class="sidebar" style="padding-left:0px;"><b>CDER - Physical</b></div>
			{+if (mj_User::$currUser->isAllowed("Queue_CEDR_ItemCreation")) || (mj_User::$currUser->isAllowed("Queue_CEDR_ViewQueues")) ||(mj_User::$currUser->isAllowed("Queue_CEDR_ListEditor"))+}
				<div class="sidebar" style="padding-left:15px;"><b>Workflow Management</b></div>
				{+if (mj_User::$currUser->isAllowed("Queue_CEDR_ViewQueues"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=newQueue.viewQueue&projName=CDER">My Work Queues</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Queue_CEDR_ListEditor"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Lists.ListCreation&projName=CDER">List Editor</a></div>
				{+/if+}
			{+/if+}
			{+if (mj_User::$currUser->isAllowed("Reports_CDER_QueueStatus")) || (mj_User::$currUser->isAllowed("Reports_CDER_Productivity")) || (mj_User::$currUser->isAllowed("Reports_CDER_IndividualProd")) || (mj_User::$currUser->isAllowed("Reports_CDER_MonthlySLA"))+}
				<div class="sidebar" style="padding-left:15px;"><b>Reports</b></div>
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_QueueStatus"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=QueueStatus">Queue Status</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_Productivity"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=Productivity">Productivity</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_IndividualProd"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=IndividualProd">My Productivity</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_MonthlySLA"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=MonthlySLA">Monthly SLA</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_Quality"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=Quality">Quality Report</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("Reports_CDER_STATS"))+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=CDER&subcmd=Stats">Stats</a></div>
				{+/if+}
			{+/if+}
		{+/if+}
		
	{+if (mj_User::$currUser->isAllowed("QCA_View_Queue")) || (mj_User::$currUser->isAllowed("QCA_View_Reports")) || (mj_User::$currUser->isAllowed("QCA_List_Edit")) || (mj_User::$currUser->isAllowed("Bor_View_Queue"))+}
	<hr width="90%" align="center">
	<br>
		<div class="sidebar " style="color:blue;"><b>CDER - Gateway</b></div>
			<div class="sidebar" style="padding-left:15px; color:blue;"><b>Workflow Management</b></div>
			{+if (mj_User::$currUser->isAllowed("QCA_View_Queue"))+}
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=newQueue.viewQueue&projName=QCA">My Work Queue</a></div>
			{+/if+}
			{+if (mj_User::$currUser->isAllowed("Bor_View_Queue"))+}
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=newQueue.viewQueue&projName=BOR">BOR Validation</a></div>
			{+/if+}
			{+if (mj_User::$currUser->isAllowed("QCA_List_Edit"))+}
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Lists.ListCreation&projName=QCA">List Editor</a></div>
			{+/if+}
			{+if (mj_User::$currUser->isAllowed("QCA_View_Reports"))+}
			<div class="sidebar" style="padding-left:15px; color:blue;"><b>Reports</b></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=QCA&subcmd=QueueStatus">Queue Status</a></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=QCA&subcmd=MonthlyReport">Monthly Report</a></div>
			{+/if+}
	{+/if+}
	
	{+if (mj_User::$currUser->isAllowed("MFlegacy_View_Queue")) || (mj_User::$currUser->isAllowed("MFLegacy_View_Reports")) || (mj_User::$currUser->isAllowed("MFLegacy_List_Edit")) || (mj_User::$currUser->isAllowed("Bor_View_Queue"))+}
	<hr width="90%" align="center">
	<br>
		<div class="sidebar " style="color:blue;"><b>DMF - Legacy</b></div>
			<div class="sidebar" style="padding-left:15px; color:blue;"><b>Workflow Management</b></div>
				{+if (mj_User::$currUser->isAllowed("MFLegacy_View_Queue"))+}
					<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=newQueue.viewQueue&projName=MFLegacy">My Work Queue</a></div>
				{+/if+}
				{+if (mj_User::$currUser->isAllowed("MFLegacy_List_Edit"))+}
					<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Lists.ListCreation&projName=MFLegacy">List Editor</a></div>
				{+/if+}
			{+if (mj_User::$currUser->isAllowed("MFLegacy_View_Reports")) || (mj_User::$currUser->isAllowed("MFLegacy_View_User_Reports"))+}
				<div class="sidebar" style="padding-left:15px; color:blue;"><b>Reports</b></div>
					<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=FindPrevious">Find Previous Scanned</a></div>
					{+if (mj_User::$currUser->isAllowed("MFLegacy_View_User_Reports"))+}
						<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=QueueStatus">Queue Status</a></div>
						<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=IndividualProd">My Productivity</a></div>
					{+/if+}
					<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=Productivity">Productivity</a></div>
					<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=MonthlyReport">Monthly Report</a></div>
					<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=MFLegacy&subcmd=WeeklyPredictions">Weekly Forecast</a></div>
			{+/if+}	
	{+/if+}	
	
	{+if (mj_User::$currUser->isAllowed("Queue_ACTCOMMS"))+}
	<hr width="90%" align="center">
		<div class="sidebar " style="color:blue;"><b>Action Communications</b></div>
			<div class="sidebar" style="padding-left:15px; color:blue;"><b>Workflow Management</b></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=newQueue.viewQueue&projName=ACTCOMS">My Work Queue</a></div>
			{+if (mj_User::$currUser->isAllowed("ACTCOMS_List_Edit"))+}
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Lists.ListCreation&projName=ACTCOMS">List Editor</a></div>
			{+/if+}
			<div class="sidebar" style="padding-left:15px; color:blue;"><b>Reports</b></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=ACTCOMS&subcmd=MonthlyReport">Monthly Report</a></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=ACTCOMS&subcmd=QueueStatus">Queue Status</a></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=ACTCOMS&subcmd=Productivity">Productivity</a></div>
				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=ACTCOMS&subcmd=IndividualProd">My Productivity</a></div>
			
	{+/if+}
<!--				<div class="sidebar" style="padding-left:30px; color:blue;"><a href="my_application.php?path=Reports.renderReport&cmd=QCA&subcmd=MonthlyReport">Monthly Report</a></div>

<!--
        <br><div class="sidebar">Dynamic Nav Items:</div><br>
-->
        {+if isset($navItemList) +}
          {+foreach from=$navItemList item=navItemGroup+}
	    {+if in_array($navItemGroup.privs,$userPrivs)+}
              {+if isset($navItemGroup.sectionTitle) && $navItemGroup.sectionTitle != ''+}
                <br>
	        <div class="sidebar"><b>{+$navItemGroup.sectionTitle+}</b></div>
              {+/if+}
              {+foreach from=$navItemGroup.links item=navItem+}
	        {+if in_array($navItem.priv,$navPrivList)+}
                  <div class="sidebar"><a href="{+$navItem.link+}" alt="{+$navItem.prompt+}">{+$navItem.prompt+}</a></div>
                {+/if+}
              {+/foreach+}
            {+/if+}
          {+/foreach+}
        {+/if+}

        <br>
    
<!--
        <div class="sidebar">&nbsp;</div>
        <div class="sidebar"><a href="page.php?page=password">Change Password</a></div>
        {+if hasAccess("user") +}
          <div class="sidebar"><a href="page.php?page=add_attendance">Add Attendance</a></div>
          <div class="sidebar"><a href="page.php?page=edit_attendance">Edit Attendance</a></div>
          <div class="sidebar"><a href="page.php?page=attendance_report">Attendance Report</a></div>
          <div class="sidebar"><a href="page.php?page=user">Manage Users</a></div>
        {+/if+}
        <div class="sidebar">&nbsp;</div>
-->

<!--
 {+if (mj_User::$currUser->isAllowed("ENRD"))+}
<hr width="90%" align="center">
	<br>
		<div class="sidebar"><b>ENRD</b></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Lists.ListCreation&projName=ENRDTransport">Delivery Location Editor</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=newQueue.viewQueue&projName=ENRDTransport">Manage Mail Delivery</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=newQueue.customForms&projName=ENRD&formName=MailPickDeliv">Pickup/Deliver Mail</a></div>
			<br>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Lists.ListCreation&projName=ENRDMail">New Mail List Editor</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=newQueue.viewQueue&projName=ENRDMail">New Mail Work Queues</a></div>
			<br>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Lists.ListCreation&projName=ENRDRequest">Request List Editor</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=newQueue.viewQueue&projName=ENRDRequest">Request Work Queues</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=newQueue.openForms&projName=ENRD&formName=WorkRequest">Request Forms</a></div>
{+/if+}
-->
		
<hr width="90%" align="center">
        <br>
        {+if (mj_User::$currUser->isAllowed("TS_User")) || (mj_User::$currUser->isAllowed("TS_Manager"))+}
          <div class="sidebar"><b>Timesheet Reports</b></div>
		{+if (mj_User::$currUser->isAllowed("TS_User"))+}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=TS.tsUserRpt&cmd=overview">My Timesheet</a></div>
<!--			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=TS.tsUserRpt">Timesheet User Report</a></div> -->
		{+/if+}
		{+if (mj_User::$currUser->isAllowed("TS_Manager"))+}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=TS.tsManagerRpt">Manager View</a></div>
<!--			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=TS.tsManagerRpt">Manager Timesheet Report</a></div> -->
		{+/if+}
        {+/if+}
        <br>  
        {+if (mj_User::$currUser->isAllowed("editUser")) || (mj_User::$currUser->isAllowed("EditGroups"))+}
          <div class="sidebar"><b>Rights Management</b></div>
		  {+if (mj_User::$currUser->isAllowed("editUser")) +}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=UserMaint.UserMaintHtml">User Edit</a></div>
		  {+/if+}
		  {+if (mj_User::$currUser->isAllowed("EditGroups")) +}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=GroupMaint.GroupMaintHtml">Group Edit</a></div>
		  {+/if+}
        {+/if+}

        <br>
        <div class="sidebar"><b>Bug Tracking</b></div>
		{+if (mj_User::$currUser->isAllowed("BugReport_ViewBugs"))+}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Queue.viewQueue&projName=P3BugReport&queueName=Triage">Triage Bug Reports</a></div>
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Queue.viewQueue&projName=P3BugReport&queueName=Working">Resolve Bug Reports</a></div>
		{+/if+}
		{+if (mj_User::$currUser->isAllowed("BugReport_UAT"))+}
			<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Queue.viewQueue&projName=P3BugReport&queueName=Working">User Acceptance Testing</a></div>
			<div class="sidebar" style="padding-left:30px;"><a href="my_application.php?path=Reports.renderReport&cmd=P3BugReport&subcmd=Status">Change Status</a></div>
		{+/if+}
	<div class="sidebar" style="padding-left:15px;"><a href="my_application.php?path=Queue.itemCreation&projName=P3BugReport">Report a Bug</a></div>
	<br>


<hr width="90%" align="center"><p>

        <div class="sidebar"><a href="my_application.php?path=UserMaint.UserMaintHtml&cmd=changePass">Change password</a></div>
	<br>
        <div class="sidebar"><a href="login.php?cmd=logout">Logout</a></div>
      {+else+}
        <div class="sidebar">&nbsp;<br><br><br><br></div>
        <div class="sidebar">&nbsp;<br><br><br><br></div>
      {+/if+}

      <div class="sidebar">&nbsp;</div>
      <div class="sidebar">{+if isset($frame_left_div1)+}{+$frame_left_div1+}{+/if+}</div>
      <div class="sidebar">{+if isset($frame_left_div2)+}{+$frame_left_div2+}{+/if+}</div>
      <div class="sidebar">{+if isset($frame_left_div3)+}{+$frame_left_div3+}{+/if+}</div>
      <div class="sidebar">&nbsp;</div>
      <div class="copyright">
        <img src=img/zai_logo50pct.jpg>
        <br>Copyright &copy; 2013 Zimmerman Associates, Inc.
      </div> 
    </td>
<!-- End Left Sidebar -->

