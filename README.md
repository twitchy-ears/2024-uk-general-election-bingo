# 2024-uk-general-election-bingo

A silly little PHP script to generate bingo cards for current Conservative MPs losing their seats in the 2024 General Election in the UK

So basically run `setup-images.sh` to create an "images" directory,
download a CSV of all the current MPs in the UK and download current
Conservative party MP images all from They Work For You (.com)

After that your index.php will happily generate simple little bingo cards.
using the local image cache you built up above and reading the data from the
CSV file.

It has some useful links at the bottom for permalinks to that index card,
smaller images, having Rishi Sunak as the Free Space, etc.

# Bugs

 - Probably secure but like don't get angry if you find an issue with it
 - Probably funny
 - Smaller images probably generates a page you can print out?  No guarentees
 - `setup-images.sh` requires `curl` and `convert` to be in your path
 - Who knows, I just hacked it out for a joke after work
