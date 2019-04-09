<?php

function getSslPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function getImages($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

if (isset($_POST['fetch'])) {
    $url = $_POST['url'];

    $data = getSslPage($url);
    $data1 = json_decode($data);

    if (null != $data1) {
        $p = $data1->product->images;

        $images = [];
        foreach ($p as $key => $value) {
            $value->src = substr($value->src, 0, strpos($value->src, '?'));
            $images[] = $value->src;
        }
        // die(var_dump($images));
        $i = 1;
        if (null != $images) {
            foreach ($images as $image) {
                $cpm = "curl -0 ${image} -o ${user_id}/images/pic${i}.jpg";
                echo $cpm;
                exec($cpm);
                ++$i;
            }

            echo "<script>
                    alert('Images Fetched Successfully');
                </script>";
        } else {
            echo "<script>
                    alert('Something went wrong! No Images found');
                </script>";
        }
    } else {
        echo "<script>
                alert('Something went wrong! Please enter valid json url');
            </script>";
    }
}
