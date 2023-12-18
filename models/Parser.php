<?php

namespace app\models;

use DiDom\Document;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use PHPUnit\Util\Log\JSON;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class Parser extends Model
{


    /* json auto models */
    public function parsingSalePage()
    {
        $models = $this->getGmContent();

        if (!empty($models)) {
            $hasNewAuto = false;
            /* сохраняем модели авто в бд */

            $autoModelCount = AutoModels::find()->count();
            $autoModelSiteCount = count($models);
            if ($autoModelCount != $autoModelSiteCount) {
                $hasNewAuto = true;
                AutoModels::deleteAll();
                foreach ($models as $model) {
                    $autoModel = new AutoModels();
                    $autoModel->model_id = $model->model_id;
                    $autoModel->name = $model->name;
                    $autoModel->save();
                }
            }

            if (!$hasNewAuto) {
                foreach ($models as $model) {
                    if (!($autoModel = AutoModels::find()->where(['model_id' => $model->model_id])->one())) {
                        $autoModel = new AutoModels();
                        $hasNewAuto = true;
                    }
                    $autoModel->model_id = $model->model_id;
                    $autoModel->name = $model->name;
                    $autoModel->save();
                }
            }

            /* если есть изминении в мтодели авто или в кол то отправим пуш на бот */
            if ($hasNewAuto) {
                $bot_api_key = Yii::$app->params['bot_api_key'];
                $bot_username = Yii::$app->params['username_bot'];

                try {
                    $message = 'GM AUTO Current models list';
                    $autoModels = AutoModels::find()->all();
                    foreach ($autoModels as $k => $model) {
                        $message .= "\n" . ($k + 1) . ' - ' . $model->name;
                    }
                    // Create Telegram API object
                    $telegram = new Telegram($bot_api_key, $bot_username);

                    $result = Request::sendMessage([
                        'chat_id' => 411213390,
                        'text' => $message . '😜',
                    ]);
                } catch (Longman\TelegramBot\Exception\TelegramException $e) {
                    // Silence is golden!
                    // log telegram errors
                    // echo $e->getMessage();
                }
            }
        }
    }

    /* parse news page */
    public function parsingNewsPage()
    {
        $document = new Document();
        $newsHtml = $this->getNewsPage();
        if ($newsHtml) {
            $document->loadHtml($newsHtml);
            $newsBlock = $document->find('.hm_filter_wrapper.masonry_posts.three_blocks ul li');
            if (empty($newsBlock)) {
                echo "empty page";
                die('empty');
            }

            $hasNews = false;
            foreach ($newsBlock as $item) {
                $link = "";
                $date = null;
                $titleString = null;

                $meta = $item->find('.meta_part');
                if (!empty($meta)) {
                    $date = $meta[0]->find('span');
                    $date = $date[0]->text();
                }
                $title = $item->find('.title a');
                if (!empty($title)) {
                    $link = $title[0]->attr('href');
                    $titleString = trim(strip_tags($title[0]->html()));
                }

                $news = News::find()->where(['link' => $link])->one();
                if (empty($news)) {
                    $news = new News();
                    $hasNews = true;
                }

                $news->link = $link;
                $news->title = $titleString;
                $news->last_date = date("Y-m-d H:i:s", strtotime($date));
                $news->save();

            }

            /* если есть изминении в мтодели авто или в кол то отправим пуш на бот */
            if ($hasNews) {
                $bot_api_key = Yii::$app->params['bot_api_key'];
                $bot_username = Yii::$app->params['username_bot'];

                try {
                    $message = 'Added news GM SITE';
                    $autoModels = News::find()->orderBy('last_date desc')->one();
                    $message .= "\n" . $autoModels->title;
                    // Create Telegram API object
                    $telegram = new Telegram($bot_api_key, $bot_username);

                    $result = Request::sendMessage([
                        'chat_id' => 411213390,
                        'text' => '🔥🔥🔥🔥🔥' . $message . '🔥🔥🔥🔥🔥',
                    ]);
                } catch (Longman\TelegramBot\Exception\TelegramException $e) {
                    // Silence is golden!
                    // log telegram errors
                    // echo $e->getMessage();
                }
            }


        }
    }

    /* news html content
        @return html
    */
    public function getNewsPage()
    {

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', 'https://uzautomotors.com/');
            $code = $response->getStatusCode(); // 200
            if ($code == 200) {
                $html = ($response->getBody());
                return $html;
            }

        } catch (GuzzleException $e) {
            print_r($e->getMessage());
        }

        return false;
    }

    /* get models json */
    public function getGmContent()
    {

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://savdo.uzavtosanoat.uz/b/ap/stream/ph&models', [
                'form_params' => [
                    'filial_id' => 100,
                    'is_web' => 'Y'
                ],
//                 "proxy" => [
//                     "http://appweb8921:8bd16b@109.248.7.220:10065",
//                     "http://appweb8921:8bd16b@109.248.7.207:10213",
//                 ],
//                RequestOptions::VERIFY => false,
            ]);
            $code = $response->getStatusCode(); // 200
            if ($code == 200) {
                $models = json_decode($response->getBody());
                print_r($models);
                return $models;

            }
        } catch (GuzzleException $e) {
            print_r($e->getMessage());
        }

        return [];

    }
}
