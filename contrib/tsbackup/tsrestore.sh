#!/bin/sh

# Run this script in your qb base directory. Supply the output of tsbackup.sh on
# stdin. The script will then touch the files contained in the list and set
# their modification time accordingly.

while read -r ts file; do
	touch -d "@$ts" "$file"
done
