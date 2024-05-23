<?php

$data_file="theyworkforyou-mps.csv";
$images_dir="images";

# echo "file is '$data_file'<br />\n";

if (! file_exists($data_file)) {
  echo "Error, go run the setup script you don't have any data yet.";
  exit(1);
}

$skipper = 0;
$mp_data;
foreach(file($data_file) as $line) {
  if ($skipper == 0) {
    $skipper += 1;
    continue;
  }

  # "Person ID","First name","Last name",Party,Constituency,URI
  $data = str_getcsv($line);

  if ($data[3] == "Conservative") {
    $person_id = $data[0];
    $mp_data[$person_id] = array(
      "person_id" => $data[0],
      "first_name" => $data[1],
      "last_name" => $data[2],
      "party" => $data[3],
      "constituency" => $data[4],
      "twfu_url" => $data[5]
      );
  }
}

# 348 is the total but 300 is just a ballpark for truncated data files
if (! is_array($mp_data) || sizeof($mp_data) < 300) {
  echo "Error loading data, probably truncated";
  exit(1);
}

$mp_ids;
$wanted_mps;
$small_images = false;
$freespace;

if (isset($_GET["small"])) {
  if ($_GET["small"] == "yes") {
    $small_images = true;
  }
}

if (isset($_GET["freespace"])) {
  $raw = $_GET["freespace"];
  $cooked = preg_replace('/[^[0-9]]/', '', $raw);
  $free_space_mp = $cooked;
}

if (isset($_GET["permalink"])) {
  $raw = $_GET["permalink"];
  $cooked = preg_replace('/[^[0-9,]]/', '', $raw);
  # echo "cooked: '$cooked'<br />\n";
  $wanted_mps = explode(",", $cooked);
}

if (! isset($wanted_mps) || ! is_array($wanted_mps) || sizeof($wanted_mps) != 24) {
  # echo "Generating random bingo<br />\n";
  # echo "also sizeof wanted_mps: " . sizeof($wanted_mps) . "<br />";
  # print_r($mp_data);
  $mp_ids = array_keys($mp_data);
  if (isset($free_space_mp)) {
    unset($mp_ids[$free_space_mp]);
  }
  shuffle($mp_ids);
  $wanted_mps = array_slice($mp_ids, 0, 24);
}

/*
   foreach($wanted_mps as $wanted_id) {
    if (isset($mp_data[$wanted_id]["first_name"]) && isset($mp_data[$wanted_id]["last_name"])) {
      print "$wanted_id " . $mp_data[$wanted_id]["first_name"] . " " . $mp_data[$wanted_id]["last_name"] . "<br />\n";
    }
    # print "$wanted_id<br />\n";
  }
 */

function print_mp_cell($mp_id, $pretext = "", $posttext = "") {
  global $mp_data;
  global $images_dir;
  global $small_images;

  $image_path="$images_dir/$mp_id.jpg";
  $image_size = "";
  if ($small_images) {
    $image_size="width=\"96\" height=\"128\"";
  }

  # echo "Processing mp $mp_id<br />\n";
  
  if (isset($mp_data[$mp_id]["first_name"])
      && isset($mp_data[$mp_id]["last_name"])
      && isset($mp_data[$mp_id]["constituency"])) 
  {
    echo "<td>";
    echo "<div style=\"text-align: center; margin: auto;\">";
    echo "$pretext<img src=\"$image_path\" $image_size /><br />" . $mp_data[$mp_id]["first_name"] . " " . $mp_data[$mp_id]["last_name"] . "<br />" . $mp_data[$mp_id]["constituency"] . "$posttext</td>\n";
  }
  else {
    echo "<td>Oopsie error for MP $mp_id</td>\n";
  }
}

echo "<html><head><title>2024 UK Election Conservatives Losing Their Seats Bingo!</title></head><body style=\"text-align: center; margin: auto;\">";
echo "<p>2024 UK Election Conservatives Losing Their Seats Bingo!</p>\n";
echo "<table style=\"margin: auto;\">\n";
echo "<tr>\n";
print_mp_cell($wanted_mps[0]);
print_mp_cell($wanted_mps[1]);
print_mp_cell($wanted_mps[2]);
print_mp_cell($wanted_mps[3]);
print_mp_cell($wanted_mps[4]);
echo "</tr><tr>\n";
print_mp_cell($wanted_mps[5]);
print_mp_cell($wanted_mps[6]);
print_mp_cell($wanted_mps[7]);
print_mp_cell($wanted_mps[8]);
print_mp_cell($wanted_mps[9]);
echo "</tr><tr>\n";
print_mp_cell($wanted_mps[10]);
print_mp_cell($wanted_mps[11]);
if (isset($free_space_mp)) {
  print_mp_cell($free_space_mp, "<b>-- FREE --</b><br/>", "<br /><b>-- SPACE --</b>");
}
else {
  echo "<td style=\"text-align: center; margin: auto; font-weight: bold;\">FREE<br />SPACE</td>\n";
}

print_mp_cell($wanted_mps[12]);
print_mp_cell($wanted_mps[13]);
echo "</tr><tr>\n";
print_mp_cell($wanted_mps[14]);
print_mp_cell($wanted_mps[15]);
print_mp_cell($wanted_mps[16]);
print_mp_cell($wanted_mps[17]);
print_mp_cell($wanted_mps[18]);
echo "</tr><tr>\n";
print_mp_cell($wanted_mps[19]);
print_mp_cell($wanted_mps[20]);
print_mp_cell($wanted_mps[21]);
print_mp_cell($wanted_mps[22]);
print_mp_cell($wanted_mps[23]);
echo "</tr></table>\n";

echo "<br /><br />";
echo "<a href=\"index.php\">GENERATE A NEW BINGO CARD</a><br />";
echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "\">PERMALINK TO THIS BINGO CARD</a><br />";
echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&freespace=25428\">PERMALINK TO THIS BINGO CARD - With Rishi Sunak as the Free Space</a><br />";
echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&small=yes\">PERMALINK TO THIS BINGO CARD - with slightly smaller images for printing on A4</a>";
echo " - ";
echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&small=yes&freespace=25428\">small with Sunak</a>";

echo "</body></html>";

?>
