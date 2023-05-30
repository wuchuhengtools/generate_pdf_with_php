build:
	docker compose build

start:
	docker compose up

generatePDF:
	doc_file=$(docker exec 9079e61815f1 /bin/bash -c 'cd /www;php src/main.php' ); open -a  /Applications/wpsoffice.app/Contents/MacOS/wpsoffice $doc_file



