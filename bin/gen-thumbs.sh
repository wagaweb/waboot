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

# Function to convert image to WebP
convert_to_webp() {
    local input_file="$1"
    local output_file="$2"

    if cwebp -preset picture -q 75 -z 6 -resize "$width" "$height" "$input_file" -o "$output_file" &> /dev/null; then
        echo "Converted: $output_file"
        return 0
    else
        echo "Error converting: $input_file"
        return 1
    fi
}

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
            if ! convert_to_webp "$file" "$output_file"; then
                echo "Attempting color space conversion..."
                temp_file="${dir}/${filename_no_ext}.rgb.${ext}"
                if convert "$file" -colorspace CMYK -colorspace RGB "$temp_file"; then
                    echo "Color space conversion successful. Retrying WebP conversion..."
                    if convert_to_webp "$temp_file" "$output_file"; then
                        rm "$temp_file"
                    else
                        echo "WebP conversion failed even after color space adjustment."
                        rm "$temp_file"
                    fi
                else
                    echo "Color space conversion failed."
                fi
            fi
        else
            echo "Skipping: $file (output already exists)"
        fi
    done
done

echo "Script execution completed."
