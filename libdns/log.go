package webpanellibdns

import (
	"flag"
	"log"
	"os"
)

var (
	Log *log.Logger
)

func init() {
	// set location of log file
	var logpath = "/var/log/caddy/libdns.log"

	flag.Parse()
	var file, err1 = os.Create(logpath)

	if err1 != nil {
		panic(err1)
	}
	Log = log.New(file, "", log.LstdFlags|log.Lshortfile)
	Log.Println("LogFile : " + logpath)
}

func GetLog() *log.Logger {
	return Log
}
