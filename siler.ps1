docker run --rm -v "$(pwd):/opt/siler" "siler:$($args[0])" $args[1..10]