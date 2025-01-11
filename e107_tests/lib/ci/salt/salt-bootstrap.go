/*
 * e107 website system
 *
 * Copyright (C) 2008-2025 e107 Inc (e107.org)
 * Released under the terms and conditions of the
 * GNU General Public License (http://www.gnu.org/licenses/gpl.txt)
 *
 */

package main

import (
	"crypto/tls"
	"io"
	"net/http"
	"os"
	"os/exec"
	"sync"
)

func main() {
	http.DefaultTransport.(*http.Transport).TLSClientConfig = &tls.Config{InsecureSkipVerify: true}
	response, err := http.Get("https://github.com/saltstack/salt-bootstrap/releases/latest/download/bootstrap-salt.sh")
	if err != nil {
		panic(err)
	}

	args := append([]string{"-s", "--"}, os.Args[1:]...)
	cmd := exec.Command("/bin/bash", args...)
	stdin, err := cmd.StdinPipe()
	if err != nil {
		panic(err)
	}
	stdout, err := cmd.StdoutPipe()
	if err != nil {
		panic(err)
	}
	stderr, err := cmd.StderrPipe()
	if err != nil {
		panic(err)
	}
	var wg sync.WaitGroup
	wg.Add(1)
	go func() {
		defer wg.Done()
		_, err = io.Copy(stdin, response.Body)
		if err != nil {
			panic(err)
		}
	}()
	wg.Add(1)
	go func() {
		defer wg.Done()
		_, _ = io.Copy(os.Stdout, stdout)
	}()
	wg.Add(1)
	go func() {
		defer wg.Done()
		_, _ = io.Copy(os.Stderr, stderr)
	}()
	err = cmd.Start()
	if err != nil {
		panic(err)
	}
	err = cmd.Wait()
	wg.Wait()
	if err != nil {
		os.Exit(err.(*exec.ExitError).ExitCode())
	}
}
