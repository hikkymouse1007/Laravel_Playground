<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class ElasticController extends Controller
{
    private $es_uri = 'https://search-public-domein-agmessljp7ban5ysgpifnteowm.ap-northeast-1.es.amazonaws.com';

    public function getData($event_id = 1)
    {
        // // # 同時視聴者数取得コマンド
        $client = new Client();
        // $uri = 'http://es-server:9200/1/_search'; //ベースURL TODO:ローカルだと_searchが400エラー
        $uri = $this->es_uri. "/{$event_id}/_search"; //ベースURL

        $params = [
            "json" =>[
                'query' => [
                    "range" =>[
                        "timestamp" => [
                        "gte" => "now+9h-1d",//現在時刻の30秒前（30秒という数値は変更の可能性有）
                        "lt"=> "now+9h"////現在時刻（gteとltで直近30秒間ログを指定）
                        ]
                    ]
                ],
                "aggs" => [
                    "_doc"=> [
                        "cardinality"=> [
                            "field"=>"user_id"//user_idのユニーク数を出力する。
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->request('GET', $uri, $params);
        $responseBody = $response->getBody()->getContents();
        $json_to_array = json_decode($responseBody, true);
        $audience_count = $json_to_array["aggregations"]["_doc"]["value"];

        return $audience_count;
    }

    public function postData($event_id = 1)
    {
        // # ユーザー毎の視聴時間カウント数取得コマンド
        //  ## ユーザー数の取得
        $client = new Client();
        $uri = $this->es_uri. "/{$event_id}/_search";
        $params = [
            "json" =>[
                "aggs"=>[
                    "distinct_name_count"=>[
                        "cardinality"=>[
                            "field"=>"user_id"//User_idのユニーク数を出力する
                        ]
                    ]
                ]
            ]
        ];
        $response = $client->request('POST', $uri, $params);
        $responseBody = $response->getBody()->getContents();
        $json_to_array = json_decode($responseBody, true);
        $audience_count = $json_to_array["aggregations"]["distinct_name_count"]["value"];
        return $audience_count;
    }

    // ## 上のユーザー数をsizeのところにセットしてコマンドを叩く
     public function getCount($event_id = 1)
     {
        $client = new Client();
        $uri = $this->es_uri. "/{$event_id}/_search";
        $params = [
            "json" =>[
                "aggs"=>[
                    "_doc"=>[
                        "terms"=>[
                            "field"=>"user_id",//User_idをKeyにする
                            "include"=>[
                                "partition"=>0,//分割データの1番目を出力する。0始まり。
                                "num_partitions"=>2//データを10分割にすることを指定。
                            ],
                            "size"=>$this->postData($event_id),//上のコマンドで取得したValueをセットする。
                            "shard_size"=>1//ESのシャード数を指定する。
                        ]
                    ]
                ]
            ]
        ];

        $response = $client->request('GET', $uri, $params);
        $responseBody = $response->getBody()->getContents();
        $json_to_array = json_decode($responseBody, true);
        dd($json_to_array);
    }



    //　サンプルコード
    public function createTable()
    {
        // # ユーザー毎の視聴時間カウント数取得コマンド
        //  ## ユーザー数の取得
        $client = new Client();
        $uri = "http://es-server:9200/test-index/_doc";
                //  ## ユーザー数の取得
        $params = [
            "json" => [
                "user_id"=>"{number}",
                "timestamp"=>"2020-10-23T15:57:00"
            ]
        ];

        $response = $client->request('POST', $uri, $params);
        $responseBody = $response->getBody()->getContents();
        return $responseBody;

    }
}
