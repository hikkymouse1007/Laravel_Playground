## 基本構成は以下の記事を参考
https://qiita.com/A-Kira/items/9a03d7b230741ed7b1de

## セットアップ手順

1. ホームディレクトリに移動してdocker-composeを起動

```
docker-compose up -d
```
2. webコンテナに入る

```
docker-compose exec web bash
```
3. laravelのインストール

```
laravel new
```
4. appディレクトリにlaravelが作成されるのを確認したらMac上のに/etc/hostsにドメインを追加
```
sudo vim /etc/hosts
#/etc/hosts 
127.0.0.1 web.localhost.com web2.localhost.com
```
5. ブラウザから http://web1.localhost.com:8000/ http://web2.localhost.com:8000/
にアクセスしてLaravelのトップ画面を確認する。

6. ホームディレクトリでdockerを止める
```
docker-compose down
```

## 参考記事
nginx-proxy/nginx-proxy
https://github.com/nginx-proxy/nginx-proxy

Dockerサービスの簡単リバースプロキシ
https://qiita.com/South_/items/7bdb1f373410cb1c907b

## Dockerのメモ
コンテナ内から他のコンテナに通信したい場合は
docker-compose.ymlにhostnameを指定する

```
// docker-compose.yml
elasticsearch:
    image: elasticsearch:7.9.3
    hostname: es-server //ホスト名
    volumes:
      - ./elasticsearch-data:/usr/share/elasticsearch/data
    ports:
      - "9200:9200"
    environment:
      - discovery.type=single-node

// 確認
$ docker exec laravel_playground_elasticsearch_1 hostname
es-server

// Laravelコントローラからの指定(PHPコンテナ内)
public function getData()
    {
        // // # 同時視聴者数取得コマンド
        $client = new Client();
        $uri = 'http://es-server:9200'; //ベースURL,ポートは9200を指定
```

## elastcisearchの記事
https://hub.docker.com/_/elasticsearch
https://www.elastic.co/guide/en/elasticsearch/reference/7.5/docker.html