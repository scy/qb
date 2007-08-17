#!/bin/bash

STATPATH=/var/www/der-dakon.net/blog/
BASEURL=http://der-dakon.net/QB/
TMPDIR=/var/www/der-dakon.net/blog_tmp/

mkdir -p ${STATPATH}

function mangle_pages {
	local FIN
	local OUTFILE

	if [ "${1}" = "?1" ]; then
		FIN="index"
	else
		if [ "$(echo ${1} | colrm 2)" = "?" ]; then
			FIN="index${1}"
		else
			FIN="${1}"
		fi
	fi

	if [ -n "$(echo ${FIN} | sed 's/[^?]*//')" ]; then
		OUTFILE=$(echo ${FIN} | sed -r "s,([^?]*)\?(.*),\1__\2,")
	else
		OUTFILE=${FIN}
	fi

	echo "${OUTFILE}"
}

function get_this {
	local GETDIR
	local OUTFILE
	local INFILE
	local FNAME

	echo "Getting recursive for ${1}"
	for i in $(grep -o '/QB/[^"#'"'"']*' ${1} | sort| uniq); do
		GETDIR=$(echo ${i} | sed 's,^/QB/,/,;s,/[^/]*$,,;s,^/,,')
		if [ -n "${GETDIR}" ]; then
			mkdir -p ${GETDIR}
			mkdir -p ${STATPATH}/${GETDIR}
#			echo ${GETDIR} created
		fi
		FNAME=$(echo ${i} | sed 's,.*/,,')
		if [ "${GETDIR}/${FNAME}" = "/index.html" ]; then
			continue;
		fi

#		echo ${GETDIR}/${FNAME}
		if [ -n "${FNAME}" ]; then
			if [ -n "${GETDIR}" ]; then
				OUTFILE="${GETDIR}/$(mangle_pages ${FNAME})"
				INFILE=${GETDIR}/${FNAME}
			else
				OUTFILE="$(mangle_pages ${FNAME})"
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
			fi
		fi
	done
	sed "s,/QB/?1',/QB/index',g" -i ${1}
	sed -r "s,/QB/\?([^']*),/QB/index?\1,g" -i ${1}
	sed -r "s,/QB/([^?]*)\?([^']*),/blog/\1__\2.html,g" -i ${1}
	sed -r "s,/QB/([^']*),/blog/\1.html,g" -i ${1}
	if [ "$(cmp ${1} ${STATPATH}/${1}.html 2>&1)" ]; then
		rm -f ${STATPATH}/${1}.html
		ln ${1} ${STATPATH}/${1}.html
		echo "${1} updated"
	fi
}

if [ ! -d ${TMPDIR} ]; then
	rm -f ${TMPDIR}
	mkdir ${TMPDIR} || exit 1
fi
cd ${TMPDIR} || exit 1
rm -rf *

wget -q -O index ${BASEURL}
get_this index

wget -q -O blog.xml ${BASEURL}/?atom10
sed -i -r "s,/QB/,/blog/,g" blog.xml
if [ "$(cmp blog.xml ${STATPATH}/blog.xml 2>&1)" ]; then
	rm -f ${STATPATH}/blog.xml
	ln blog.xml ${STATPATH}/blog.xml
	echo "blog.xml updated"
fi

rm -rf ${TMPDIR}
