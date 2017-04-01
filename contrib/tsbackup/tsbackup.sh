#!/bin/sh

# Run this script in your qb base directory. For each .qb or .cre file in src/
# and meta/, it will output the modification timestamp of that file, followed by
# a space character and then the path to the file. Timestamps are given in
# seconds since the Unix epoch.

find src meta \
	\( -name '*.qb' -o -name '*.cre' \) \
	-printf '%T@ %p\n' \
| sort -n -k 1
