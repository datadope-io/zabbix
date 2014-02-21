#!/bin/bash

cd $(dirname $0)
source settings.sh

if [ -n "$PID_FILE" -a -e "$PID_FILE" ]; then
	echo "Zabbix Java Gateway is already running"
	exit 1
fi

JAVA=${JAVA:-java}

JAVA_OPTIONS="-server"
if [ -z "$PID_FILE" ]; then
	JAVA_OPTIONS="$JAVA_OPTIONS -Dlogback.configurationFile=logback-console.xml"
fi

CLASSPATH="lib"
for jar in {lib,bin}/*.jar; do
	CLASSPATH="$CLASSPATH:$jar"
done

ZABBIX_OPTIONS=""
if [ -n "$PID_FILE" ]; then
	ZABBIX_OPTIONS="$ZABBIX_OPTIONS -Dzabbix.pidFile=$PID_FILE"
fi
if [ -n "$LISTEN_IP" ]; then
	ZABBIX_OPTIONS="$ZABBIX_OPTIONS -Dzabbix.listenIP=$LISTEN_IP"
fi
if [ -n "$LISTEN_PORT" ]; then
	ZABBIX_OPTIONS="$ZABBIX_OPTIONS -Dzabbix.listenPort=$LISTEN_PORT"
fi
if [ -n "$START_POLLERS" ]; then
	ZABBIX_OPTIONS="$ZABBIX_OPTIONS -Dzabbix.startPollers=$START_POLLERS"
fi

COMMAND_LINE="$JAVA $JAVA_OPTIONS -classpath $CLASSPATH $ZABBIX_OPTIONS com.zabbix.gateway.JavaGateway"

if [ -n "$PID_FILE" ]; then

	# start the gateway and output pretty errors to the console

	STDOUT=$($COMMAND_LINE & echo $! > "$PID_FILE")
	if [ -n "$STDOUT" ]; then
		echo "$STDOUT"
	fi

	# verify that the gateway started successfully

	PID=$(cat "$PID_FILE")
	if ! ps -p "$PID" > /dev/null 2>&1; then
		echo "Zabbix Java Gateway did not start"
		rm -f "$PID_FILE"
		exit 1
	fi

else
	exec $COMMAND_LINE
fi
