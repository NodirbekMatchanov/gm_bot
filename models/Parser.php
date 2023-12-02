<?php

namespace app\models;

use DiDom\Document;
use GuzzleHttp\Exception\GuzzleException;
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

    public  function parsingSalePage() {
//        $document = new Document();
//        $document->html();
        $models = $this->getGmContent();
        if(!empty($models)) {
            $hasNewAuto = false;
            foreach ($models as $model) {
                if(!($autoModel = AutoModels::find()->where(['model_id' => $model->model_id])->one())) {
                    $autoModel = new AutoModels();
                    $hasNewAuto = true;
                }
                $autoModel->model_id = $model->model_id;
                $autoModel->name = $model->name;
                $autoModel->save();
            }

            if(!$hasNewAuto) {
                $autoModelCount = AutoModels::find()->count();
                $autoModelSiteCount = count($models);
                if($autoModelCount != $autoModelSiteCount) {
                    $hasNewAuto = true;
                }
            }

            if($hasNewAuto) {
                $bot_api_key  = Yii::$app->params['bot_api_key'];
                $bot_username = Yii::$app->params['username_bot'];

                try {
                    $message = 'GM AUTO Current models list';
                    $autoModels = AutoModels::find()->all();
                    foreach ($autoModels as $k => $model) {
                        $message .= "\n". ($k+1).' - '. $model->name;
                    }
                    // Create Telegram API object
                    $telegram = new Telegram($bot_api_key, $bot_username);

                    $result = Request::sendMessage([
                        'chat_id' => 411213390,
                        'text'    => $message.'😜',
                    ]);
                } catch (Longman\TelegramBot\Exception\TelegramException $e) {
                    // Silence is golden!
                    // log telegram errors
                    // echo $e->getMessage();
                }
            }
        }
    }

    public function parsingNewsPage() {

    }

    public function getGmContent() {

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', 'https://savdo.uzavtosanoat.uz/b/ap/stream/ph&models',[
                'form_params' => [
                    'filial_id' => 100,
                    'is_web' => 'Y'
                ]
            ]);
            $code = $response->getStatusCode(); // 200
            if($code == 200) {
                $models = json_decode($response->getBody());
                return $models;

            }
        } catch (GuzzleException $e)
        {
            print_r($e->getMessage());
        }

        return [];

    }
}
