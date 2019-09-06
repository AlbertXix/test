#!/bin/sh
#svn propset svn:ignore -R -F .svnignore .
#svn propset svn:ignore -R *.class .
echo "svn propset svn:ignore $1"
svn propset svn:ignore "$1"
