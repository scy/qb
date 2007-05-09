#!/bin/bash

STATPATH=/var/www/der-dakon.net/blog/
BASEURL=http://der-dakon.net/QB/
TMPDIR=/var/www/der-dakon.net/blog_tmp/

function get_this {
	local GETDIR
	local OUTFILE
	local INFILE
	local FNAME

	echo "Getting recursive for ${1}"
	for i in $(grep -o '/hesasys-2.2.0/[^"#]*' ${1} | sort| uniq); do
		GETDIR=$(echo ${i} | sed 's,^/hesasys-2.2.0/,/,;s,/[^/]*$,,;s,^/,,')
		if [ -n "${GETDIR}" ]; then
			mkdir -p ${GETDIR}
			mkdir -p ${STATPATH}/${GETDIR}
#			echo ${GETDIR} created
		fi
		FNAME=$(echo ${i} | sed 's,.*/,,')
		if [ "${GETDIR}/${FNAME}" = "/index.html" ]; then
			continue;
		fi
if [ "${FNAME}" = "unfaelle_list" ]; then continue; fi
if [ "${FNAME}" = "unfall" ]; then continue; fi
if [ "${FNAME}" = "aunfall" ]; then continue; fi
if [ "${FNAME}" = "index" ]; then continue; fi
#		echo ${GETDIR}/${FNAME}
		if [ -n "${FNAME}" ]; then
			if [ -n "${GETDIR}" ]; then
				OUTFILE=${GETDIR}/${FNAME}
				INFILE=${GETDIR}/${FNAME}
			else
				OUTFILE=${FNAME}
				INFILE=${FNAME}
			fi
		else
			OUTFILE=${GETDIR}/index.html
			INFILE=${GETDIR}/
		fi
		if [ ! -s "${OUTFILE}" ]; then
			wget -q -O ${OUTFILE} ${BASEURL}${INFILE}
			if [ -s "${OUTFILE}" ]; then
				get_this ${OUTFILE}
				sed 's,/hesasys-2.2.0/,/,g;s,"/index","/index.html",g' -i ${OUTFILE}
				if [ "$(cmp ${OUTFILE} ${STATPATH}/${OUTFILE} 2>&1)" ]; then
					rm -f ${STATPATH}/${OUTFILE}
					ln ${OUTFILE} ${STATPATH}/${OUTFILE}
					echo "${OUTFILE} updated"
				fi
			fi
		fi
	done
}

if [ ! -d ${TMPDIR} ]; then
	rm -f ${TMPDIR}
	mkdir ${TMPDIR} || exit 1
fi
cd ${TMPDIR} || exit 1
rm -rf *

wget -q -O index2.html ${BASEURL}
get_this index2.html
sed 's,/QB/,/blog/,g' -i index2.html
mv index2.html index.html
if [ "$(cmp -s index.html ${STATPATH}/index.html 2>&1)" ]; then
echo bla
	rm -f ${STATPATH}/index.html
	ln index.html ${STATPATH}/index.html
	echo "index.html updated"
fi
