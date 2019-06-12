#include <stdio.h>
#include <unistd.h>
#include <sys/types.h>  
#include <sys/socket.h>  
#include <netinet/in.h>  
#include <arpa/inet.h>  
#include <assert.h>  
#include <sys/wait.h>
#include <string.h>
#include <errno.h>
#include <stdlib.h>
#include <fcntl.h>

#define IP   "127.0.0.1"
#define PORT  8888
#define WORKER 4
#define MAXLINE   4096

int worker(int i)
{
    struct sockaddr_in address;  
    bzero(&address, sizeof(address));  
    address.sin_family = AF_INET;  
    inet_pton( AF_INET, IP, &address.sin_addr);  
    address.sin_port = htons(PORT);  

    int listenfd = socket(PF_INET, SOCK_STREAM, 0);  
    assert(listenfd >= 0);  

    int val =1;
    /*set SO_REUSEPORT*/
    if (setsockopt(listenfd, SOL_SOCKET, SO_REUSEPORT, &val, sizeof(val))<0) {
        perror("setsockopt()");         
    }    
    int ret = bind(listenfd, (struct sockaddr*)&address, sizeof(address));  
    assert(ret != -1);  

    ret = listen(listenfd, 5);  
    assert(ret != -1);  
    while (1) {
        printf("I am worker %d, begin to accept connection.\n", i);
        struct sockaddr_in client_addr;  
        socklen_t client_addrlen = sizeof( client_addr );  
        int connfd = accept( listenfd, ( struct sockaddr* )&client_addr, &client_addrlen );  
        if (connfd != -1) {
            printf("worker %d accept a connection success. ip:%s, prot:%d\n", i, inet_ntoa(client_addr.sin_addr), client_addr.sin_port);
        } else {
            printf("worker %d accept a connection failed,error:%s", i, strerror(errno));
        }
        char buffer[MAXLINE];
        int nbytes = read(connfd, buffer, MAXLINE);
        printf("read from client is:%s\n", buffer);
        write(connfd, buffer, nbytes);
        close(connfd);
    }
    return 0;
}

int main()
{
    int i = 0;
    for (i = 0; i < WORKER; i++) {
        printf("Create worker %d\n", i);
        pid_t pid = fork();
        /*child  process */
        if (pid == 0) {
            worker(i);
        }
        if (pid < 0) {
            printf("fork error");
        }
    }
    /*wait child process*/
    while (wait(NULL) != 0)
        ;
    if (errno == ECHILD) {
        fprintf(stderr, "wait error:%s\n", strerror(errno));
    }
    return 0;
}