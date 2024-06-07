# 2024-uk-general-election-bingo

A silly little PHP script to generate bingo cards for current Conservative MPs losing their seats in the 2024 General Election in the UK, as a note it generates from the list of Tories who held their seats as of the dissolving of Parliament on 2024-05-30, many of these will not be standing again however its more about Tory controlled seats than anything else.

# Setup

Download a copy of the repo onto a Debian/Ubuntu flavoured webserver and try
the following:

```
$ setup-images.sh
$ composer require dompdf/dompdf
```

# Explanation 

The `setup-images.sh` script will create an "images" directory, then
download a CSV of all the current MPs in the UK and download current
Conservative party MP images all from They Work For You (.com) as of the date
of the dissolving of parliament, it will also base64 these images for
inclusion in PDF downloads.

You also need to use composer to get a copy of
[Dompdf](https://github.com/dompdf/dompdf) for generating PDF files to
download, if you'd rather install this by hand knock yourself out but you may
need to change the use line that includes it.

After that your index.php will happily generate simple little bingo cards.
using the local image cache you built up above and reading the data from the
CSV file.

It has some useful links at the bottom for permalinks to that index card,
smaller images, having Rishi Sunak as the Free Space, and downloading a one
page PDF of your bingo card.

# Bugs

 - Probably secure but like don't get angry if you find an issue with it
 - Probably funny
 - Smaller images probably generates a page you can print out?  No guarentees
 - `setup-images.sh` requires `curl`, `convert`, and `base64` to be in your path and probably be the Debian/Ubuntu flavoured ones.
 - Who knows, I just hacked it out for a joke after work
