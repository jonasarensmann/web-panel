package main

import (
	"encoding/json"
	"fmt"
	"os"
	"strings"
)

func printCaddyfile(config map[string]interface{}, indent string) {
	for key, value := range config {
		switch v := value.(type) {
		case string:
			fmt.Println(indent, key, v)
		case []interface{}:
			strs := make([]string, len(v))
			for i, item := range v {
				strs[i] = item.(string)
			}
			fmt.Println(indent, key, strings.Join(strs, " "))
		case map[string]interface{}:
			// if the key has a "@" in it, it should be split so it results in handle @name { ... }
			// otherwise, it should be just be handle {
			if strings.Contains(key, "handle@") {
				keyParts := strings.Split(key, "@")
				fmt.Println(indent, keyParts[0], "@"+keyParts[1], "{")
				delete(v, key)
			}

			// if the key has any amount of underscores in front of it, it should just be the key without the underscores
			keyParts := strings.Split(key, "_")
			if len(keyParts) > 1 {
				for strings.HasPrefix(key, "_") {
					key = strings.TrimPrefix(key, "_")
				}
			}

			super, hasSuper := v["super"]
			if hasSuper {
				switch superValue := super.(type) {
				case string:
					fmt.Println(indent, key, superValue, "{")
				case []interface{}:
					superStrs := make([]string, len(superValue))
					for i, item := range superValue {
						superStrs[i] = item.(string)
					}
					fmt.Println(indent, key, strings.Join(superStrs, " "), "{")
				}
				delete(v, "super")
			} else if !strings.Contains(key, "handle@") {
				fmt.Println(indent, key, "{")
			}
			printCaddyfile(v, indent+"  ")
			fmt.Println(indent, "}")
		case bool:
			if v {
				fmt.Println(indent, key)
			}
		}
	}
}

func main() {
	if len(os.Args) < 2 {
		fmt.Println("Error: No JSON data provided.")
		os.Exit(1)
	}

	data := strings.Join(os.Args[1:], " ")

	var config map[string]interface{}
	err := json.Unmarshal([]byte(data), &config)
	if err != nil {
		fmt.Println(err)
	}

	for site, siteConfig := range config {
		fmt.Println(site, "{")
		printCaddyfile(siteConfig.(map[string]interface{}), "  ")
		fmt.Println("}")
	}
}
