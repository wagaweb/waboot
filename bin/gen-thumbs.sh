#!/bin/bash

# Function to display usage
usage() {
    echo "Usage: $0 --path <path> --width <width> --height <height> --extensions <ext1,ext2,...> [--recreate]  [--error-file <path>]"
    exit 1
}

# Initialize variables
path=""
width=""
height=""
extensions=""
recreate=false
error_file=""

# Parse named arguments
while [[ "$#" -gt 0 ]]; do
    case $1 in
        --path) path="$2"; shift ;;
        --width) width="$2"; shift ;;
        --height) height="$2"; shift ;;
        --extensions) extensions="$2"; shift ;;
        --recreate) recreate=true ;;
        --error-file) error_file="$2"; shift ;;
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

# Function to log error
log_error() {
    local file="$1"
    if [[ -n "$error_file" ]]; then
        echo "$file" >> "$error_file"
    fi
}

# Function to convert image to WebP
convert_to_webp() {
    local input_file="$1"
    local output_file="$2"

    if cwebp -preset picture -q 75 -z 6 -resize "$width" "$height" "$input_file" -o "$output_file" &> /dev/null; then
        echo "Converted: $output_file"
        return 0
    else
        echo "Error converting: $input_file"
        log_error "$input_file"
        return 1
    fi
}

# Function to generate output filename
generate_output_filename() {
    local dir="$1"
    local filename_no_ext="$2"
    local suffix=""

    if [[ $width -ne 0 && $height -ne 0 ]]; then
        suffix="-${width}x${height}"
    elif [[ $width -ne 0 ]]; then
        suffix="-w${width}"
    elif [[ $height -ne 0 ]]; then
        suffix="-h${height}"
    fi

    echo "${dir}/${filename_no_ext}${suffix}.webp"
}

# Process files with specified extensions
echo "Processing files..."
IFS=',' read -ra ext_array <<< "$extensions"
for ext in "${ext_array[@]}"; do
    find "$path" -type f -iname "*.$ext" | while read -r file; do
        dir=$(dirname "$file")
        filename=$(basename "$file")
        filename_no_ext="${filename%.*}"
        #output_file="${dir}/${filename_no_ext}-${width}x${height}.webp"
        output_file=$(generate_output_filename "$dir" "$filename_no_ext")

        if [ "$recreate" = true ] && [ -f "$output_file" ]; then
            echo "Deleting existing file: $output_file"
            rm "$output_file"
        fi

        if [ ! -f "$output_file" ]; then
            echo "Converting: $file"
            if ! convert_to_webp "$file" "$output_file"; then
                echo "Attempting color space conversion..."
                temp_file="${dir}/${filename_no_ext}.rgb.${ext}"
                # it seems sRGB colorspace (instead of RGB) give better results
                # a ".icc" profile could be included: https://www.imagemagick.org/discourse-server/viewtopic.php?t=16464
                if convert "$file" -colorspace CMYK -colorspace sRGB "$temp_file"; then
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
