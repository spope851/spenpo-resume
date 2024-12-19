#!/bin/bash

# First: chmod +x prepare.sh
# Usage: ./compress.sh /path/to/directory [output.zip] [blacklist.txt] [-t]

# Input validation
if [ $# -lt 1 ]; then
  echo "Usage: $0 /path/to/directory [output.zip] [blacklist.txt] [-t]"
  exit 1
fi

SOURCE_DIR=$1
OUTPUT_ZIP=${2:-prepared.zip}       # Default output file name
BLACKLIST_FILE=${3:-blacklist.txt} # Default blacklist file name
ADD_TIMESTAMP=false               # Timestamp flag default

# Check for the -t flag
if [[ "$*" == *"-t"* ]]; then
  ADD_TIMESTAMP=true
fi

# Add a timestamp if the -t flag is used
if [ "$ADD_TIMESTAMP" = true ]; then
  TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
  OUTPUT_ZIP="${OUTPUT_ZIP%.zip}_$TIMESTAMP.zip" # Insert timestamp before the .zip extension
fi

# Check if the blacklist file exists (if provided)
if [ ! -f "$BLACKLIST_FILE" ]; then
  echo "Warning: Blacklist file $BLACKLIST_FILE not found. Proceeding without exclusions."
  BLACKLIST_FILE=""
fi

# Read blacklist patterns into an array (if the file exists)
EXCLUDE_ARGS=()
if [ -n "$BLACKLIST_FILE" ]; then
  while IFS= read -r pattern || [ -n "$pattern" ]; do
    EXCLUDE_ARGS+=("-x" "$pattern")
  done < "$BLACKLIST_FILE"
fi

# Compress the directory, excluding blacklisted patterns
zip -r "$OUTPUT_ZIP" "$SOURCE_DIR" "${EXCLUDE_ARGS[@]}"

if [ $? -eq 0 ]; then
  echo "Directory $SOURCE_DIR compressed to $OUTPUT_ZIP successfully."
else
  echo "Failed to compress $SOURCE_DIR."
  exit 1
fi
