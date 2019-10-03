docker build -t siler:7.2 --build-arg version=7.2 .
docker build -t siler:7.3 --build-arg version=7.3 .
docker build -t siler:7.4 --build-arg version=7.4-rc .

docker tag siler:7.3 siler