## 動作確認手順

コンソールを 3 つ開く

### サーバ動作ログ用コンソール

パイプを作成して読み込み

    rm ~/pipe
    mkfifo ~/pipe
    cat ~/pipe

### サーバ起動及びワーカーの動作ログコンソール

サーバの動作ログをパイプにリダイレクトして起動

    php sig-server.php 2> ~/pipe

### クライアント用コンソール

サーバに送信

    php sig-client.php
