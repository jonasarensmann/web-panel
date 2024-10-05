package main

import (
	"fmt"
	"net"
	"net/http"
	"os"
	"os/exec"
	"time"

	"github.com/fatih/color"
)

var isInstalling bool = false
var currentStatus string = "not started"

func getRoot(w http.ResponseWriter, r *http.Request) {
	http.ServeFile(w, r, "index.html")
}

func checkIsInstalling(w http.ResponseWriter, r *http.Request) {
	fmt.Fprintf(w, "%v", isInstalling)
}

func executeShellScript(script string, args ...string) bool {
	scriptWithArgs := append([]string{script}, args...)

	cmd := exec.Command("/bin/bash", scriptWithArgs...)
	output, err := cmd.Output()
	if err != nil {
		os.WriteFile("/tmp/install-"+script+".log", []byte(err.Error()), 0644)
		fmt.Println(fmt.Sprint(err) + ": " + err.Error())
		return false
	}
	os.CreateTemp("/tmp", "install-"+script+".log")
	os.WriteFile("/tmp/install-"+script+".log", output, 0644)
	fmt.Println(string(output))

	return true
}

func getInstall(w http.ResponseWriter, r *http.Request) {
	if r.Method != "POST" {
		fmt.Printf("Error: invalid method\n")
		os.Exit(1)
	}

	domain := r.FormValue("domain")
	username := r.FormValue("username")
	password := r.FormValue("password")

	if isInstalling {
		fmt.Printf("already installing\n")
		return
	}
	isInstalling = true

	currentStatus = "Installing Caddy"
	success := executeShellScript("scripts/install-caddy.sh", domain, username)
	if !success {
		currentStatus = "failed to install caddy | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installing Docker"
	success = executeShellScript("scripts/install-docker.sh")
	if !success {
		currentStatus = "failed to install docker | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installing php"
	success = executeShellScript("scripts/install-php.sh")
	if !success {
		currentStatus = "failed to install php | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Adding User"
	success = executeShellScript("scripts/add-user.sh", username, password)
	if !success {
		currentStatus = "failed to add user | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installing Bind"
	success = executeShellScript("scripts/install-bind.sh", domain, GetLocalIP().String())
	if !success {
		currentStatus = "failed to install bind | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installing Panel"
	success = executeShellScript("scripts/install-panel.sh", domain, username, password)
	if !success {
		currentStatus = "failed to install panel | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installting Postfix and Dovecot"
	success = executeShellScript("scripts/install-postfix-dovecot.sh", domain, password, username)
	if !success {
		currentStatus = "failed to install postfix and dovecot | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "Installing ttyd"
	success = executeShellScript("scripts/install-ttyd.sh")
	if !success {
		currentStatus = "failed to install ttyd | view terminal for more informaton"
		time.Sleep(5 * time.Second)
		os.Exit(1)
		return
	}

	currentStatus = "1"

	time.Sleep(5 * time.Second)

	os.Exit(0)
}

func getInstallStatus(w http.ResponseWriter, r *http.Request) {
	fmt.Fprint(w, currentStatus)
}

func GetLocalIP() net.IP {
	conn, err := net.Dial("udp", "8.8.8.8:80")
	if err != nil {
		panic(err)
	}
	defer conn.Close()

	localAddress := conn.LocalAddr().(*net.UDPAddr)

	return localAddress.IP
}

func main() {
	argsWithoutProg := os.Args[1:]

	if len(argsWithoutProg) > 0 && argsWithoutProg[0] == "--help" {
		fmt.Println("Usage: installer <domain> <username> <password>")
		os.Exit(0)
		return
	}

	if len(argsWithoutProg) == 3 {

		domain := argsWithoutProg[0]
		username := argsWithoutProg[1]
		password := argsWithoutProg[2]

		fmt.Println("Starting installation...")
		fmt.Println("Domain: %s\n", domain)
		fmt.Println("Username: %s\n", username)
		fmt.Println("Please wait until the Process exists")

		executeShellScript("scripts/install-caddy.sh", domain, username)
		executeShellScript("scripts/install-docker.sh")
		executeShellScript("scripts/install-php.sh")
		executeShellScript("scripts/add-user.sh", username, password)
		executeShellScript("scripts/install-bind.sh", domain, GetLocalIP().String())
		executeShellScript("scripts/install-panel.sh", domain, username, password)
		executeShellScript("scripts/install-postfix-dovecot.sh", domain, password, username)
		executeShellScript("scripts/install-ttyd.sh")

		os.Exit(0)
		return
	}

	http.HandleFunc("/", getRoot)
	http.HandleFunc("/install", getInstall)
	http.HandleFunc("/install/status", getInstallStatus)
	http.HandleFunc("/install/check", checkIsInstalling)

	color.Green("Visit the following URL to continue with the installation:")

	fmt.Printf("http://%s:3333\n", GetLocalIP())
	err := http.ListenAndServe(":3333", nil)
	if err != nil {
		fmt.Printf("Error: %s\n", err)
		os.Exit(1)
	}
}
