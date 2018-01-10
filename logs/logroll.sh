#!/bin/sh

LOGNAME=$1
if [ "$LOGNAME" = "" ]
then
  echo ERROR:
  echo "   Invalid commandline options: log file name required"
  exit
fi

mv $LOGNAME $LOGNAME.`date +%Y-%m-%d-%H-%M`
touch $LOGNAME
chmod a+rw $LOGNAME


