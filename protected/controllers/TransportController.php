<?php
class TransportController extends Controller
{
    // main Page
    public function actionI()
    {
        $lastRates = array();
        $criteria = new CDbCriteria();
        $criteria->compare('status', 1);
        if(!Yii::app()->user->isGuest && Yii::app()->user->isTransport) {
            $userInfo = UserField::model()->findByAttributes(array('user_id'=>Yii::app()->user->_id));
            if((int)$userInfo->show_intl && !(int)$userInfo->show_regl) $criteria->compare('type', 0);
            if((int)$userInfo->show_regl && !(int)$userInfo->show_intl) $criteria->compare('type', 1);
        }
        
        $dataProvider = new CActiveDataProvider('Transport',
            array(
                'criteria' => $criteria,
                'pagination'=>array(
                   'pageSize' => 10,
                   'pageVar' => 'page',
                ),
                'sort'=>array(
                    'defaultOrder'=>array(
                        'date_close' => CSort::SORT_ASC,
                        'location_from' => CSort::SORT_ASC,
                        'location_to' => CSort::SORT_ASC,
                    ),                        
                ),
            )
        );

        $this->render('user.views.transport.view', array('data' => $dataProvider, 'title'=>'Все перевозки'));
    }

    // show one Transport
    public function actionDescription($id)
    {
        if(!Yii::app()->user->isGuest) 
        {
            $transport = Transport::model()->findByPk($id);
            if (!$transport) {
                 throw new CHttpException(404,Yii::t('yii','Страница не найдена'));
            }
            
            $currency = '€';
            if($transport->currency == Transport::RUB){
               $currency = 'руб.';
            } else if($transport->currency == Transport::USD){
               $currency = '$';
            }
            
            $priceStep = Transport::INTER_PRICE_STEP;
            if(!$transport->currency){
                $priceStep = Transport::RUS_PRICE_STEP; 
            }
            
            $model = new Rate;
            $criteria = new CDbCriteria;
            $criteria->select = 'min(price) AS price, id, user_id';
            $criteria->condition = 'transport_id = :id';
            $criteria->params = array(':id'=>$id);
            $minPrice = $model->model()->find($criteria);

            if(!empty($minPrice['price'])){
                $crtr = new CDbCriteria;
                $crtr->select = 'id, user_id';
                $crtr->order = 'date';
                $crtr->condition = 'transport_id = :id and price like :price';
                $crtr->params = array(':id'=>$id, ':price'=>$minPrice['price']);
                $row = $model->model()->find($crtr);
            
                if(!empty($row['id'])) {
                    $transport = Transport::model()->findByPk($id);
                    if($transport->rate_id != $row['id']) {
                        $transport->rate_id = $row['id'];
                        $transport->save();
                    }
                }
            }
            
            $allPoints = TransportInterPoint::getPoints($id, $transport->location_to);
            
            if(!Yii::app()->user->isTransport) // admin   
            {
                $showWinner = '';
                $minRateValue = $transport->start_rate;
                if (!empty($transport->rate_id)) {
                    $minRateValue = floor($this->getMinPrice($id));
                    
                    $winRate = Rate::model()->findByPk($transport->rate_id);                
                    $winFerryman = User::model()->findByPk($winRate->user_id);
                    $winFerrymanShowNds = UserField::model()->findByAttributes(array('user_id'=>$winRate->user_id));
                    
                    $showWinner = $winFerryman->company;
                    if($winFerrymanShowNds->with_nds && $transport->type == Transport::RUS_TRANSPORT) {
                        $price = ceil($winRate->price + $winRate->price * Yii::app()->params['nds']);
                        if($price%10 != 0) $price -= $price%10;
                        $showWinner = $showWinner . ' (с НДС: ' . $price . ' ' . $currency . ') ';    
                    }
                }
                
                $this->render('user.views.transport.itemForAdmin', array(
                    //'allRates' => $allRates,
                    'transport' => $transport,
                    'allPoints' => $allPoints,
                    'currency' => $currency,
                    'priceStep' => $priceStep,
                    'minRateValue'=>$minRateValue,
                    'showWinner' => $showWinner
                ));
            } else       
                $this->render('user.views.transport.item', array(
                    'transport' => $transport,
                    'allPoints' => $allPoints,
                    'currency' => $currency,
                    'priceStep' => $priceStep
                ));
        } else {
            $this->redirect('/user/login/');
        }
    }

    /* Ajax update rate for current transport */
    public function actionUpdateRates()
    {
        $id = $_POST['id'];
        $price = '';
        //$newPrice = $_POST['newRate'];
        //$priceStep = $_POST['step'];
        $error = 0;
        
        /*if($newPrice) {
            $elementExitsts = Rate::model()->find(array(
                'condition'=>'price = :price AND transport_id = :id',
                'params'=>array(':price' => (int)$newPrice, ':id' => $id),
            ));
            
            if(empty($elementExitsts)) {
                $obj = array(
                    'transport_id'  => $id,
                    'user_id' => Yii::app()->user->_id,
                    'date'    => date("Y-m-d H:i:s"),
                    'price'   => (int)$newPrice
                );

                $modelRate = new Rate;
                $modelRate->attributes = $obj;
                $modelRate->save();

                $model = Transport::model()->findByPk($id);
                $rateId = $model->rate_id;

                // send mail
                if(!empty($rateId)){ // empty when don't have rates
                    $rateModel = Rate::model()->findByPk($rateId);
                    $this->mailKillRate($rateId, $rateModel);
                    $this->siteKillRate($rateId, $rateModel);
                }

                $model->rate_id = $modelRate->id;
                $model->save();
            } else {
                $error = 1;
            }
        }
         */
        
        $data = Yii::app()->db->createCommand()
            ->select('r.*, u.company, u.name, u.surname, f.with_nds')
            ->from('rate r')
            ->join('user u', 'r.user_id=u.id')
            ->join('user_field f', 'f.user_id=u.id')
            ->where('r.transport_id=:id', array(':id'=>$id))
            ->order('r.date asc, r.price desc')
            ->queryAll()
        ;
                
        foreach($data as $k=>$v){
           $data[$k]['time']=date('d.m.Y H:i:s', strtotime($v['date']));
        }
        
        if(count($data)) {
            $sql = 'select price from rate where transport_id = '.$id.' group by transport_id order by date desc limit 1';
            $price = Yii::app()->db->createCommand($sql)->queryScalar();
            if(($price - $priceStep) <= 0) {
                Transport::model()->updateByPk($id, array('status'=>0));
            }
        }

        $array = array('price'=>$price, 'all'=>$data, 'error' => $error);
        echo json_encode($array);
    }
	
    public function addFormat($date)
    {
        if((int)$date < 10) $date = '0' . $date;
            return $date;
    }

    /* Get latest price for current transport */
    public function getPrice($id)
    {
        $row = Yii::app()->db->createCommand()
            ->select('price')
            ->from('rate')
            ->where('id = :id', array(':id' => $id))
            ->queryScalar()
        ;
        return $row;
    }
    
    public function getMinPrice($id)
    {
        $row = Yii::app()->db->createCommand()
            ->select('min(price) as price')
            ->from('rate')
            ->where('transport_id = :id', array(':id' => $id))
            ->queryScalar()
        ;
        return $row;
    }
	
    // Send mail to user if his rate was killed
    public function mailKillRate($rateId, $rateModel)
    {
        $users = array();
        $temp = Yii::app()->db->createCommand()
                ->select('user_id')
                ->from('user_field')
                ->where('mail_kill_rate = :type', array(':type' => true))
                ->queryAll()
        ;
        foreach($temp as $t){
                $users[] = $t['user_id'];
        }

        if(in_array($rateModel->user_id, $users)){
            $userModel = User::model()->findByPk($rateModel->user_id);
            $transportModel = Transport::model()->findByPk($rateModel->transport_id);
            $email = new TEmail;
            $email->from_email = Yii::app()->params['adminEmail'];
            $email->from_name  = 'Биржа перевозок ЛБР АгроМаркет';
            $email->to_email   = $userModel->email;
            $email->to_name    = '';
            $email->subject    = 'Уведомление';
            $email->type = 'text/html';
            $email->body = '<h1>Уважаемый(ая) ' . $userModel->name . ' ' . $userModel->surname .',</h1>
              <div>
                  <p>Вашу ставку для перевозки "<a href="http://exchange.lbr.ru/transport/description/id/'.$rateModel->transport_id.'">' . $transportModel->location_from . ' &mdash; ' . $transportModel->location_to . '</a>" перебили. </p>
              </div>
              <h5>Это автоматическое уведомление, на него не следует отвечать.</h5>
            ';
            $email->sendMail();
        }
    }
    
    public function siteKillRate($rateId, $rateModel)
    {
        $users = array();		
        $temp = Yii::app()->db->createCommand()
            ->select('user_id')
            ->from('user_field')
            ->where('site_kill_rate = :type', array(':type' => true))
            ->queryAll()
        ;
        foreach($temp as $t){
            $users[] = $t['user_id'];
        }

        if(in_array($rateModel->user_id, $users)){
            $obj = array(
                'user_id' => $rateModel->user_id,
                'transport_id' => $rateModel->transport_id,
                'status' => 1,
                'type' => 1,
                'event_type' => 5,
            );

            Yii::app()->db->createCommand()->insert('user_event',$obj);
        }
    }
    
    public function getPoints($id)
    {
        $points = '';        
        $innerPoints = Yii::app()->db->createCommand()
            ->select('point')
            ->from('transport_inter_point')
            ->where('t_id=:id', array(':id'=>$id))
            ->order('date')
            ->queryAll()
        ;
        
        foreach($innerPoints as $point){
            if(isset($points)) $points .= ' -> ';
            $points .= $point['point'];
        }
        return $points;
    }
    
    public function actionCheckForAdditionalTimer()
    {
        $id = $_POST['id'];
        $now = date('m/d/Y H:i:s');
        $date = Yii::app()->db->createCommand()
            ->select('date_close_new')
            ->from('transport')
            ->where('id=:id', array(':id'=>$id))
            ->queryScalar()
        ;
        if(!empty($date) && date('m/d/Y H:i:s', strtotime($date)) > $now) $date = date('m/d/Y H:i:s', strtotime($date));
        else $date = '';
        
        $array = array('end'=>$date, 'now'=>$now);
        echo json_encode($array);
    }
    
    public function actionCheckForTransportStatus()
    {
        $id = $_POST['id'];
        $status = Yii::app()->db->createCommand()
            ->select('status')
            ->from('transport')
            ->where('id=:id', array(':id'=>$id))
            ->queryScalar()
        ;
        echo $status;
    }
    
    public function actionGetCurTime()
    {
        $updateTimeInMilliseconds = 10*60*1000; // 10 min
        $curDate = date('m/d/Y H:i:s');
        $endDate = date('m/d/Y H:i:s', strtotime($_POST['endDate']));

        $hoursLeft = floor((strtotime($endDate) - strtotime($curDate))/(60*60));
        if(!$hoursLeft){
            $minLeft = floor((strtotime($endDate) - strtotime($curDate))/60);
            if($minLeft < 1) $updateTimeInMilliseconds = 5*1000; // 5 sec
            else if($minLeft < 3) $updateTimeInMilliseconds = 20*1000; // 20 sec
            else if($minLeft < 10) $updateTimeInMilliseconds = 2*60*1000; // 2 min
            else if($minLeft < 30) $updateTimeInMilliseconds = 5*60*1000; // 5 min
        }

        $array = array('date' => $curDate, 'minUpdate' => $updateTimeInMilliseconds, 'end'=>$endDate);
        echo json_encode($array);
    }
    
    public function actionGetEvents()
    {
        $count = Yii::app()->db->createCommand()
            ->select('count(*)')
            ->from('user_event')
            ->where('user_id=:id and status=1', array(':id'=>$_POST['userId']))
            ->queryScalar()
        ;
        echo $count;
    }
}