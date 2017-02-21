package main

import (
	"net/http"
	"fmt"
	"log"
	"io/ioutil"
	"time"
	"regexp"
	"flag"
	"math/rand"
	"encoding/base64"
	"strconv"
)
var (
	Version = "0.0.2"
	ip138_url string = "http://www.ip138.com/ips138.asp"
	api_url string = "http://test.com/get_data.php"
	my_ua string	 = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36"
	http_port string = "127.0.0.1:8001"
)

const (
	random_key = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"
	random_len = 3
)

/**
返回数据
 */
func ReturnContent(w http.ResponseWriter,r *http.Request) {
	r.ParseForm()
	w.Header().Set("content-type", "text/html")
	w.Header().Set("server", "go")
	var return_content string = ""
	req_path := r.URL.Path
	log.Println("Req_path: ",req_path)
	if req_path == "/get_net_ip.php"{
		params_env := r.Form["env"]
		log.Println("Req Params: ",params_env)

		ip := SendCurl("ip138","")
		log.Println("Return ip : ",ip)
		if len(params_env) ==1 {
			if ip == ""  {
				if params_env[0] ==  "c" {
					return_content = "fail"
				} else if params_env[0] ==  "brw" {
					return_content = "var g_net_ip=null;"
				}
			} else {
				if params_env[0] ==  "brw" {
					return_content = "var g_net_ip="+"'"+ip+"';"
				} else if params_env[0] ==  "c" {
					return_content = ip
				}
			}
		} else {
			return_content = "Request Params error"
		}

	} else if req_path == "/get_client_info"  {
		params_ip := r.Form["netip"]
		if len(params_ip) == 1 {

			return_c := SendCurl("",str_encode(params_ip[0]))
			return_cookie := str_decode(return_c)

			if len(return_cookie) == 0 {
				return_content = "fail"
			} else {
				return_content = return_cookie
			}


		} else {
			return_content = "Request Params error"
		}
	} else {
		return_content = "404 NOT FOUND"
	}
	w.Write([]byte(return_content))

}
/**
发送http请求
 */
func SendCurl(domain string,param string) string {

	client := &http.Client{
		Timeout: 30 * time.Second,
	}
	request_url := api_url+"?req="+param
	if domain == "ip138" {
		request_url = ip138_url
	}
	req, err := http.NewRequest("GET", request_url, nil)
	if err != nil {
		log.Println("NewRequest: ",err)
		return ""
	}
	if domain == "ip138" {
		req.Header.Set("Accept", "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8")
		req.Header.Set("Host","www.ip138.com")
	}
	req.Header.Set("User-Agent",my_ua)
	req.Header.Set("Accept-Encoding","gzip, deflate, sdch")
	req.Header.Set("Accept-Language","zh-CN,zh;q=0.8")
	req.Header.Set("Cache-Control","no-cache")
	resp, err := client.Do(req)
	if err != nil {
		log.Println("HttpClient: ",err)
		return ""
	}
	defer resp.Body.Close()

	body, err := ioutil.ReadAll(resp.Body)
	if err != nil {
		log.Println("HttpBody: ",err)
		return ""
	}
	if domain == "ip138" {
		reg := regexp.MustCompile(`ip_add\.asp\?ip=([0-9\.]+)\">`)
		match := reg.FindStringSubmatch(string(body))
		if len(match) == 2 {
			return match[1]
		}else{
			log.Println("not match ip")
			return ""
		}
	} else{
		return string(body)
	}
}
/**
加密请求数据
 */
func str_encode(s string) string{
	ss 	  := str_random(random_len)+s
	encoded   := base64.StdEncoding.EncodeToString([]byte(ss))
	encoded_r := strconv.FormatInt(time.Now().Unix(),10)+str_reverse(encoded)
	encoded_n := base64.StdEncoding.EncodeToString([]byte(encoded_r))
	return encoded_n
}
/**
解密请求数据
 */
func str_decode(s string) string{
	decoded,err := base64.StdEncoding.DecodeString(s)
	if err != nil {
		log.Println("First decode error:  ",err)
		return  ""
	}

	ss := str_reverse(string(decoded)[10:])

	decoded_n,err_n := base64.StdEncoding.DecodeString(ss)
	if err != nil {
		log.Println("Seconed decode error:  ",err_n)
		return  ""
	}
	return string(decoded_n[3:])
}
/**
随机生成字符串
 */
func str_random(n int) string{
	rand.Seed(time.Now().UTC().UnixNano())
	b := make([]byte,n)
	for i := range b{
		b[i] = random_key[rand.Intn(len(random_key))]
	}
	return string(b)
}
/**
反转字符串
 */
func str_reverse(s string) string{
	r := []rune(s)
	for i,j := 0,len(r)-1; i < len(r)/2; i,j= i+1,j-1 {
		r[i],r[j] = r[j],r[i]
	}
	return string(r)
}
func main() {
	version := flag.Bool("v",false,"version")
	flag.Parse()
	if *version {
		fmt.Println(Version)
	} else {
		http.HandleFunc("/",ReturnContent)
		err := http.ListenAndServe(http_port,nil)
		if err != nil {
			log.Println("ListenAndServe: ",err)
		}

	}

}
