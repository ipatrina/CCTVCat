<?php

date_default_timezone_set("Asia/Shanghai");
error_reporting(E_ALL & ~E_WARNING);

$channelIds = [
    "600002309",
    "600002475",
    "600002521",
    "600002520",
    "600002483",

    "600002481",
    "600002532",
    "600002505",
    "600002513",
    "600002531",

    "600002498",
    "600002506",
    "600002490",
    "600002516",
    "600002503",

    "600002509",
    "600002525",
    "600002485",
    "600002484",
    "600002508",

    "600002493",
    "600014550",
    "600084781",
    "600084744",
    "600084704",

    "600084782",
    "600084758",
    "v1",
];

$channelNames = [
    "北京卫视",
    "湖南卫视",
    "江苏卫视",
    "浙江卫视",
    "东方卫视",

    "深圳卫视",
    "安徽卫视",
    "辽宁卫视",
    "山东卫视",
    "重庆卫视",

    "黑龙江卫视",
    "海南卫视",
    "贵州卫视",
    "四川卫视",
    "江西卫视",

    "广西卫视",
    "河南卫视",
    "广东卫视",
    "福建东南卫视",
    "湖北卫视",

    "河北卫视",
    "CGTN",
    "CGTN 纪录",
    "CGTN 西班牙语",
    "CGTN 法语",

    "CGTN 阿拉伯语",
    "CGTN 俄语",
    "更多频道",
];

function yspGet($h, $c, $d)
{
    curl_setopt(
        $h,
        CURLOPT_URL,
        "http://capi.yangshipin.cn/api/yspepg/program/" . $c . "/" . $d
    );
    curl_setopt($h, CURLOPT_AUTOREFERER, true);
    curl_setopt($h, CURLOPT_TIMEOUT, 30);
    curl_setopt($h, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($h, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($h, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($h, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($h, CURLOPT_TCP_KEEPALIVE, 1);
    $html = curl_exec($h);

    $resultArray = [];
    foreach (explode("\x0A", $html) as $item) {
        $progName = "";
        $progTime = "";
        $pointer = strpos($item, "\x12");
        if ($pointer !== false && strlen($item) > 10) {
            $length = ord(substr($item, $pointer + 1, 1));
            $progName = substr($item, $pointer + 2, $length);
            $pointer += 2 + $length + 4;
            $item2 = substr($item, $pointer);
            while ($pointer < strlen($item)) {
                $length = ord(substr($item, $pointer + 1, 1));
                if (ord(substr($item, $pointer, 1)) == 42) {
                    $progTime = substr($item, $pointer + 2, $length);
                    break;
                }
                $pointer += 2 + $length;
            }
        }
        if (strlen($progName) > 0 && strlen($progTime) > 0) {
            array_push($resultArray, [
                "title" => $progName,
                "startTime" => $progTime,
            ]);
        }
    }
    return $resultArray;
}

function formatDate($date)
{
    return substr($date, 0, 4) .
        "-" .
        substr($date, 4, 2) .
        "-" .
        substr($date, 6, 2);
}

function getChannelName($channelId)
{
    global $channelIds;
    global $channelNames;
    $index = array_search($channelId, $channelIds);
    if ($index === false) {
        return "未知频道";
    } else {
        return $channelNames[$index];
    }
}

function getWeek($time = "", $format = "Ymd")
{
    global $service;
    if ($service == "null") { return array(date('Ymd'), date('Ymd')); }
    $time = $time != "" ? $time : time();
    $week = date("w", $time);
    if ($week == 0) {
        $week = 7;
    }
    $date = [];
    for ($i = 1; $i <= $week; $i++) {
        $date[$i] = date(
            $format,
            strtotime($i - $week . " days", $time)
        );
    }
    for ($i = 1; $i <= 7; $i++) {
        $day = date("Ymd", strtotime($i . " days"));
        if (!in_array($day, $date)) {
            $date[count($date) + 1] = $day;
        }
    }
    return $date;
}

if (isset($_GET["Service"])) {
    $service = $_GET["Service"];
} else {
    $service = "null";
}

$date = getWeek();
if ($service == "null") { $service = $channelIds[0]; }

?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>CCTVCat</title>
<style>
body {
    position: relative;
    margin: 0 auto;
    padding: 0px;
    width: 1000px;
    font-family: Microsoft YaHei UI, Microsoft YaHei, SimHei, DejaVu Sans, Trebuchet MS, Verdana;
}

h3 {
	background-color: yellow;
	padding: 5px;
	margin-bottom: 5px;
	margin-top: 20px;
	word-wrap: break-word;
	word-break: normal;
	font-size: 16px;
	text-align: center;
}

h4 {
	line-height: 20px;
	margin: 5px;
}

p {
	font-weight: bold;
	font-size: 14px;
	line-height: 1px;
}

td {
	padding: 1px;
	text-align: center;
}

a {
	text-decoration: none;
}

.heading {
	color: purple;
}

.subHeading {
	color: blueviolet;
	font-size: 16px;
}

#top {
    position: fixed;
    bottom: 12%;
    font-size: 12px;
    cursor: pointer;
    margin-left: 1020px;
}
</style>
</head>

<body>
<br><br>
<div style="width: 1000px; height: 125px;">
	<div style="width: 150px; height: 100px; float: left; margin-top: 15px;">
	    <img src="data:image/png;base64,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" width="150" height="100" />
	</div>
	<div style="width: 840px; height: 100px; float: right;">
		<table width="840" height="100">
		  <tbody>
<?php
print "<tr>";
for ($i = 1; $i <= count($channelIds); $i++) {
    print '<td style="background-color: lightblue; width: 14.3%;"><p><a class="heading" href="' .
            ($channelIds[$i - 1] == "v1"
                ? "../v1/"
                : "./?Service=" . $channelIds[$i - 1]) .
            '">' .
        $channelNames[$i - 1] .
        "</a></p></td>";
    if ($i % 7 == 0) {
        print "</tr><tr>";
    }
}
print "</tr>";
?>
		  </tbody>
		</table>
	</div>
</div>

<?php
function printEpgTable($epgData, $epgChannelName)
{
    print '<table width="1000">';
    for ($j = 1; $j <= count($epgData); $j++) {
        print "<tr>";
        print '<td style="width: 350px; text-align: left;"><h4>' .
            $epgChannelName . " " .
            $epgData[$j - 1]["startTime"] .
            ":00</h4></div>";
        print '<td style="width: 650px; text-align: left;"><h4>' .
            $epgData[$j - 1]["title"] .
            "</h4></div>";
        print "</tr>";
    }
    print "</table>";
}

$currentChannelName = getChannelName($service);
$curlHandle = curl_init();
for ($i = 1; $i <= count($date); $i++) {
    $currentTable = yspGet($curlHandle, $service, $date[$i]);
    if (count($currentTable) > 0) {
        print '<h3 class="subHeading">'.formatDate($date[$i]).'</h3>';
        printEpgTable(
            $currentTable,
            $currentChannelName . " " . formatDate($date[$i])
        );
    }
}
curl_close($curlHandle);
?>

<div style="display:none" id="top" title="返回顶部">
    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAgCAYAAABkWOo9AAAAY0lEQVR42u3YzQ0AEAyG4e6/i31MYA2cnEV/orxNen9Cv0aIzCq19ZtbMiAXFihQoED3+rSAPjuj2pMLgVpdsyvUeiZdoF4BMoV6p90EGrWa/tyjKcIEFChQXvhAgQLlR0+LHB4iJFdgD290AAAAAElFTkSuQmCC"/>
</div>
<script>
    function goTopEx(){
        var obj = document.getElementById("top");
        function getScrollTop() {
            return document.documentElement.scrollTop || document.body.scrollTop;
        }
        window.onscroll = function() {
            getScrollTop() > 1000 ? obj.style.display = "" : obj.style.display = "none";
        }
        obj.onclick = function() {
            window.scrollTo('0', '0');
        }
    }
    
    goTopEx();
</script>
<h5 style="text-align: center; font-size: 14px; color: green;">CCTVCat 2.0.1</h5>
</body>
</html>