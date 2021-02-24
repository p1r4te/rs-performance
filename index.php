<?php
session_start();
$users = array(
    '5f757fd6bd1298006fbfe687' => 'QA Mazikin',
    'edgar.simonyan' => 'QA',
    'yreutova' => 'QA',
    '5e5e6a93bbb5110c9a691e79' => 'QA Kuzminova',
    'abatakov' => 'DEV',
    'andreyrodko' => 'DEV',
    'drubanov' => 'DEV',
    'ekoshel' => 'DEV',
    'atarasov' => 'DEV',
    'iknyazhesky' => 'DEV',
    'sandruschak' => 'DEV',
    'aleksandrdergelev' => 'DEV',
    '88fd63c1-b4e2-40d1-8c09-05631b00540c' => 'OPS',
    'anvodola' => 'OPS',
    );

$year = strftime('%Y');
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-01');
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

function checkLogin()
{
    $encodeAccount = base64_encode("{$_POST['myusername']}:{$_POST['mypassword']}");

    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Basic '.$encodeAccount;

    $ch = curl_init("https://acesse.atlassian.net");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
var_dump($response);die;

    return true;
}

function getAccount()
{
    if (isset($_POST['myusername']) && isset($_POST['mypassword']) && checkLogin()) {
        $_SESSION['login'] = $_POST['myusername'];
        $_SESSION['password'] = $_POST['mypassword'];
    }

    return $_SESSION['login'] . ':' . $_SESSION['password'];
}

function getTotalFromJira($url)
    {
        $ch = curl_init($url);
        $account = getAccount();
        if ($account == ':') {
            header('Location: login.php');
        }
        $encodeAccount = base64_encode($account);

        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Basic '.$encodeAccount;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $json = json_decode($response, true);
        $total = isset($json['total'])?$json['total']:0;
        return $total;
    }

function getTotal($user, $start_date, $end_date)
{
    $url_total = "https://acesse.atlassian.net/rest/api/2/search?maxResults=0&fields=id&jql=assignee+changed+to+({$user})+during+({$start_date},{$end_date})";
    return getTotalFromJira($url_total);
}

function getClosed($user, $start_date, $end_date)
{
    $url_closed = "https://acesse.atlassian.net/rest/api/2/search?maxResults=0&fields=id&jql=status+was+Closed+BY+({$user})+AND+status+changed+DURING+({$start_date},{$end_date})";
    return getTotalFromJira($url_closed);
}

function getResolved($user, $start_date, $end_date)
{
    $url_resolved = "https://acesse.atlassian.net/rest/api/2/search?maxResults=0&fields=id&jql=status+was+in+(Verified,Resolved)+by+({$user})+AND+updatedDate<={$end_date}+AND+updatedDate>={$start_date}+AND+status+was+in+(Verified,Resolved)+before+{$end_date}+AND+status+was+in+(Verified,Resolved)+after+{$start_date}";
    return getTotalFromJira($url_resolved);
}

$total = $closed = $resolved = [];
$sum = ['total' => 0, 'closed' => 0, 'resolved' => 0];

foreach ($users as $user => $dep)
{
    $total[$user] = getTotal($user, $start_date, $end_date);
    $sum['total']+=$total[$user];
    $closed[$user] = getClosed($user, $start_date, $end_date);
    $sum['closed']+=$closed[$user];
    $resolved[$user] = getResolved($user, $start_date, $end_date);
    $sum['resolved']+=$resolved[$user];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Employee performance</title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/bootstrap-datetimepicker.min.css" rel="stylesheet">
    <link href="css/table.css" rel="stylesheet">
    <link href="css/bootstrap-table.css" rel="stylesheet">
    <link href="css/footer.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
    body {
        padding-top: 70px;
        /* Required padding for .navbar-fixed-top. Remove if using .navbar-static-top. Change if height of navigation changes. */
    }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                </button>
                <a class="navbar-brand" href="#"><img alt="Acesse" src="img/logo.png"></a>

            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="https://acesse.com">Acesse</a></li>
                    <li><a href="https://acesse.atlassian.net/projects/AM/issues/">Jira</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                     <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </div>
        <!-- /.container -->
    </nav>

    <!-- Page Content -->
    <div class="container">
        <div class="row">
        <div class="col-sm-12">
            <form method="post" class="form-inline">
                <div class="form-group">
		<div class='input-group date' id='datetimepicker6'>
                    <input type="text" class="form-control" id="start_date" name="start_date" value="<?=$start_date?>">          
		<span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
                </span>
            </div>
                    </div>
                    <div class="form-group">
		    <div class='input-group date' id='datetimepicker7'>
                    <input type="text" class="form-control" id="end_date" name="end_date" value="<?=$end_date?>">
		<span class="input-group-addon">
                    <span class="glyphicon glyphicon-calendar"></span>
            </div>
                    <input type="submit" class="btn btn-default" value="Submit">
                </div>
            </form>
            <br>
            <hr>
        <div class="col-sm-12">
                <table class="table table-bordered table-striped" style="margin: 0;" data-toggle="table">
                   <caption><h4>&nbsp Employee performance table</h4></caption>
                <thead>
		<th data-field="dep" data-sortable="true">Department</th>
                <th data-field="name" data-sortable="true">Employee Name</th>
                <th data-field="total" data-sortable="true">Issues Total</th>
                <th data-field="closed" data-sortable="true">Issues Closed</th>
                <th data-field="resolved" data-sortable="true">Issues Resolved/Verified</th>
                </thead>
                <tbody>
            <?php if ($start_date and $end_date):?>
            <div class="alert alert-info" role="alert">Period from <?=$start_date?> to <?=$end_date?></div>
            <?php endif?>
            <?php foreach ($users as $user => $dep):?>
            <tr>
		<td>
		    <?=$dep?>
		</td>
                <td>
                    <?=$user?>
                </td>
                <td>
                    <?=$total[$user]?>
                </td>
                <td>
                    <?=$closed[$user]?>
                </td>
                <td>
                    <?=$resolved[$user]?>
                </td>
            </tr>
            <?php endforeach?>
            </tbody>
        </table>
	<table class="table table-bordered" style="margin: 0;">
                <tbody>
            <tr>
		<td>
		</td>
                <td>
			<b>TOTAL:</b>
                </td>
                <td align="left">
                    <?=$sum['total']?>
                </td>
                <td>
                    <?=$sum['closed']?>
                </td>
                <td>
                    <?=$sum['resolved']?>
                </td>
            </tr>
            </tbody>
        </table>
	<br>
        <div class="col-sm-6">
	<div class="panel panel-default">
  <div class="panel-body">
    Description
	<ul>
		<li>
		<b>Issues Total</b> means the count of tickets were assigned to user in current period.
		</li>
		 <li>
		<b>Issues Closed</b> means the count of tickets were resolved by user and closed in current period.
                </li>
		 <li>
		<b>Issues Resolved</b> means the count of tickets were resolved/verified by user in current period.
                </li>
	</ul>
  </div>
</div>
</div>
        <div class="col-sm-6"><img src="img/futurama.jpg" class="img-responsive img-rounded" alt="Futurama"></div>
    </div>
    </div>
        <!-- /.row -->

    </div>
<footer class="footer-basic-centered">
        <p class="footer-company-motto">Simply do your best.</p>
        <p class="footer-company-name">Rollersoft &copy; <?= $year ?></p>
</footer>
    <!-- /.container -->

    <!-- jQuery Version 1.11.1 -->
    <script src="js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <script src="js/Moment.js"></script>
    <script src="js/bootstrap-datetimepicker.js"></script>
    <script src="js/bootstrap-table.js"></script>
   
	<script type="text/javascript">
    $(function () {
        $('#datetimepicker6').datetimepicker({
		format: 'YYYY-MM-DD'	
	});
        $('#datetimepicker7').datetimepicker({
            useCurrent: false, //Important! See issue #1075
	    format: 'YYYY-MM-DD'		
        });
        $("#datetimepicker6").on("dp.change", function (e) {
            $('#datetimepicker7').data("DateTimePicker").minDate(e.date);
        });
        $("#datetimepicker7").on("dp.change", function (e) {
            $('#datetimepicker6').data("DateTimePicker").maxDate(e.date);
        });
    });
</script>
</body>
</html>
