#!/bin/bash

# Define the process name you want to monitor
process_name="python3"

# Define the memory threshold in kilobytes (1GB = 1048576KB)
memory_threshold=800000

# Find the first occurrence of the specified process
pid=$(pgrep "$process_name" | head -n 1)

# Check if the process is running
if [ -n "$pid" ]; then
    # Get the memory usage of the process in kilobytes
    mem_usage=$(pmap -x "$pid" | grep total | awk '{print $4}')

    # Check if memory usage exceeds the threshold
    if [ "$mem_usage" -gt "$memory_threshold" ]; then
        echo "Process $process_name with PID $pid is using more than 800 MB of memory."
        cd /var/www/voice.oxyac.dev/ &&  docker-compose restart opentts
        # Add your action here, for example, send an alert or kill the process:
        # kill -9 "$pid"
    else
        echo "Process $process_name with PID $pid memory usage is within limits."
    fi
else
    echo "Process $process_name is not running."
fi
