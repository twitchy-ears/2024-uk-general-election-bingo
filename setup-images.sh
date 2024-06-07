#!/usr/bin/bash

url="https://www.theyworkforyou.com/mps/?f=csv&date=2024-05-30"
target="theyworkforyou-mps.csv"
base_dir=$( pwd -P )  
images_dir="$base_dir/images"
image_url_base="https://www.theyworkforyou.com/people-images/mpsL/"
image_url_fallback_base="https://www.theyworkforyou.com/people-images/mps/"
image_url_suffix=".jpg"
image_url_fallback_suffix=".jpeg"
image_url_total_fail="https://www.theyworkforyou.com/images/unknownperson_large.png"
image_base64_suffix=".base64.txt"

if [[ ! -x $( type -P curl ) ]]; then
  echo "No curl binary in path, failing"
  exit 1
fi

if [[ ! -x $( type -P convert ) ]]; then
  echo "No convert binary in path, failing"
  exit 1
fi

if [[ ! -d "$images_dir" ]]; then
  mkdir -p "$images_dir"
fi

if [[ ! -f "$target" ]]; then 
  curl -L "$url" > $target
fi

mapfile -t < <( tail -n +2 "$target" )
for line in "${MAPFILE[@]}"; do
  # "Person ID","First name","Last name",Party,Constituency,URI
  person_id=$( echo "$line" | cut -d, -f1 )
  first_name=$( echo "$line" | cut -d, -f2 )
  last_name=$( echo "$line" | cut -d, -f3 )
  party_name=$( echo "$line" | cut -d, -f4 )
  constituency=$( echo "$line" | cut -d, -f5 )
  twfu_uri=$( echo "$line" | cut -d, -f6 )

  # if [[ "$person_id" -ne "24934" ]]; then
  #   echo "dull was $person_id not 24934"
  #   continue
  # fi

  picture_path="${images_dir}/${person_id}${image_url_suffix}"

  if [[ "$party_name" == "Conservative" && ! -e "$picture_path" ]]; then
    cd "$images_dir"

    picture_url="${image_url_base}${person_id}${image_url_suffix}"
    picture_file="${person_id}${image_url_suffix}"
    base64_file="${person_id}${image_base64_suffix}"

    # Fetch the file in a fail early state
    echo "Fetching: \"$picture_url\" ($first_name $last_name - $party_name)"
    curl -L --fail-with-body "$picture_url" >> "$picture_file"
    curl_err=$?

    file_check=$( file --mime-type "$picture_file" | grep -c image/ )
    
    # Fallback methods of fetching the file checking for errors/missing etc.
    if [[ "$curl_err" -ne "0" || ! -f "$picture_file" || "$file_check" -ne "1" ]]; then
      fallback_picture_url="${image_url_fallback_base}${person_id}${image_url_fallback_suffix}"
      fallback_picture_file="${person_id}${image_url_fallback_suffix}"

      echo "Failed to fetch original (curl returned '$curl_err' and '$picture_file' didn't exist or is broken) so fetching \"$fallback_picture_url\""

      if [[ -e "$picture_file" ]]; then
        rm $picture_file
      fi

      curl -L --fail-with-body "$fallback_picture_url" >> "$picture_file"
      curl_err=$?
      file_check=$( file --mime-type "$picture_file" | grep -c image/ )

      if [[ "$curl_err" -ne "0" || ! -f "$picture_file" || "$file_check" -ne "1" ]]; then
        echo "Failed again, downloading '$image_url_total_fail' now"
        curl -L --fail-with-body "$image_url_total_fail" > "$picture_file.fail"
        convert "$picture_file.fail" "$picture_file"
        rm "$picture_file.fail"
      fi
    fi

    # If we have a file base64 encode it for later PDF generation
    file_check=$( file --mime-type "$picture_file" | grep -c image/ )
    if [[ -e "$picture_file" && "$file_check" -eq 1 ]]; then
      base64 -w 0 "$picture_file" >> "$base64_file"
    fi

  fi

done
