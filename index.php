<?php

/*
   Before you run this install dompdf to get PDF stuff available
   by running this command in the base directory:
   $ composer require dompdf/dompdf
 */
require 'vendor/autoload.php'; # Load in https://github.com/dompdf/dompdf to generate PDFs
use Dompdf\Dompdf;

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

# 300 chosen at random as a spot for a truncated file
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

# Set the small images for PDFs so they all fit on one A4 sheet.
$generate_pdf = false;
if (isset($_GET["pdf"])) {
  $generate_pdf = true;
  $small_images = true;
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
  global $generate_pdf;

  $output = "";

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
    $output .= "<td>";
    $output .= "<div style=\"text-align: center; margin: auto;\">";

    $mp_name = $mp_data[$mp_id]["first_name"] . " " . $mp_data[$mp_id]["last_name"];
    $img_tag = "<img src=\"$image_path\" $image_size alt=\"Picture of $mp_name\" />";

    # If we're generating a PDF then hook in the images as direct
    # Base64 streams so the PDF is portable, these have been
    # precreated by setup-images.sh
    if ($generate_pdf) {
      $image_path = "$images_dir/$mp_id.base64.txt";
      $img_tag = "<img src=\"data:image/jpeg;base64," . file_get_contents($image_path) . "\" alt=\"Picture of $mp_name\" $image_size />";
    }
    
    $output .= $pretext . $img_tag . "<br />" . $mp_name . "<br />" . $mp_data[$mp_id]["constituency"] . "$posttext</td>\n";
  }
  else {
    $output .= "<td>Oopsie error for MP $mp_id</td>\n";
  }

  return $output;
}

function generate_output() {
  global $wanted_mps;
  global $free_space_mp;
  
  $output = "<html><head><title>2024 UK Election Conservatives Losing Their Seats Bingo!</title></head><body style=\"text-align: center; margin: auto;\">";
  $output .= "<p style=\"font-size:120%; font-weight: bold;\">2024 UK Election Conservatives Losing Their Seats Bingo!</p>\n";
  $output .= "<table style=\"margin: auto;\">\n";
  $output .= "<tr>\n";
  $output .= print_mp_cell($wanted_mps[0]);
  $output .= print_mp_cell($wanted_mps[1]);
  $output .= print_mp_cell($wanted_mps[2]);
  $output .= print_mp_cell($wanted_mps[3]);
  $output .= print_mp_cell($wanted_mps[4]);
  $output .= "</tr><tr>\n";
  $output .= print_mp_cell($wanted_mps[5]);
  $output .= print_mp_cell($wanted_mps[6]);
  $output .= print_mp_cell($wanted_mps[7]);
  $output .= print_mp_cell($wanted_mps[8]);
  $output .= print_mp_cell($wanted_mps[9]);
  $output .= "</tr><tr>\n";
  $output .= print_mp_cell($wanted_mps[10]);
  $output .= print_mp_cell($wanted_mps[11]);
  
  if (isset($free_space_mp)) {
    $output .= print_mp_cell($free_space_mp, "<b>-- FREE --</b><br/>", "<br /><b>-- SPACE --</b>");
  }
  else {
    $output .= "<td style=\"text-align: center; margin: auto; font-weight: bold;\">FREE<br />SPACE</td>\n";
  }

  $output .= print_mp_cell($wanted_mps[12]);
  $output .= print_mp_cell($wanted_mps[13]);
  $output .= "</tr><tr>\n";
  $output .= print_mp_cell($wanted_mps[14]);
  $output .= print_mp_cell($wanted_mps[15]);
  $output .= print_mp_cell($wanted_mps[16]);
  $output .= print_mp_cell($wanted_mps[17]);
  $output .= print_mp_cell($wanted_mps[18]);
  $output .= "</tr><tr>\n";
  $output .= print_mp_cell($wanted_mps[19]);
  $output .= print_mp_cell($wanted_mps[20]);
  $output .= print_mp_cell($wanted_mps[21]);
  $output .= print_mp_cell($wanted_mps[22]);
  $output .= print_mp_cell($wanted_mps[23]);
  $output .= "</tr></table>\n";

  return $output;
}

$out = generate_output();
if (! $generate_pdf) {
  echo $out;

  echo "<br /><br />";
  echo "<a href=\"index.php\">GENERATE A NEW BINGO CARD</a><br />";
  echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&pdf=true\">DOWNLOAD AS PDF</a><br />\n";
  echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "\">Permalink to this Bingo Card</a><br />\n";
  echo "<br />\n";
  echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&freespace=25428\">Permalink to this Bingo Card - With Rishi Sunak as the Free Space</a><br />";
  echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&small=yes\">Permalink to this Bingo Card - with slightly smaller images for printing on A4</a>";
  echo " - ";
  echo "<a href=\"index.php?permalink=" . join(",", $wanted_mps) . "&small=yes&freespace=25428\">small with Sunak</a><br />\n";

  
  echo "</body></html>";
}

# Output the PDF
else {

  $dompdf = new Dompdf();
  $dompdf->loadHtml($out . "</body></html>");
  $dompdf->setPaper('A4', 'portrait');
  $dompdf->render();
  $dompdf->stream();
}

?>
