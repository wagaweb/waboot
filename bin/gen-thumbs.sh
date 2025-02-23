#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 --path <path> --width <width> --height <height> --extensions <ext1,ext2,...> [--recreate]"
    exit 1
}

# Initialize variables
path=""
width=""
height=""
extensions=""
recreate=false

# Parse named arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --path) path="$2"; shift ;;
        --width) width="$2"; shift ;;
        --height) height="$2"; shift ;;
        --extensions) extensions="$2"; shift ;;
        --recreate) recreate=true ;;
        *) usage ;;
    esac
    shift
done

# Check if all required arguments are provided
if [[ -z "$path" || -z "$width" || -z "$height" || -z "$extensions" ]]; then
    echo "Error: Missing required arguments."
    usage
fi

# Display the entered values
echo "Path: $path"
echo "Size: ${width}x${height}"
echo "Extensions: $extensions"
echo "Recreate: $recreate"

# Check if the path exists
if [ ! -d "$path" ]; then
    echo "Error: The specified path does not exist."
    exit 1
fi

# Process files with specified extensions
echo "Processing files..."
IFS=',' read -ra ext_array <<< "$extensions"
for ext in "${ext_array[@]}"; do
    find "$path" -type f -iname "*.$ext" | while read -r file; do
        dir=$(dirname "$file")
        filename=$(basename "$file")
        filename_no_ext="${filename%.*}"
        output_file="${dir}/${filename_no_ext}-${width}x${height}.webp"

        if [ "$recreate" = true ] && [ -f "$output_file" ]; then
            echo "Deleting existing file: $output_file"
            rm "$output_file"
        fi

        if [ ! -f "$output_file" ]; then
            echo "Converting: $file"
            if cwebp -preset picture -q 75 -z 6 -resize "$width" "$height" "$file" -o "$output_file" &> /dev/null; then
                echo "Converted: $output_file"
            else
                echo "Error converting: $file"
            fi
        else
            echo "Skipping: $file (output already exists)"
        fi
    done
done

echo "Script execution completed."