<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
    $submit_text = 'Сохранить';
    $name = $model->id;
    $delete_button = CHtml::link('Удалить', '/user/default/deletecontact/id/'.$model->id, array('id'=>'del_'.$model->name,'class'=>'btn del', 'onclick'=>'return confirm("Внимание! Контактное лицо будет безвозвратно удалено. Продолжить?")'));
    $header_form = 'Редактирование контактного лица "' . $model->surname . ' ' . $model->name . '"';
    $action = '/user/default/editcontact/id/'.$model->id;
    if (!$model->id) {
        $submit_text = 'Подтвердить';
        $header_form = 'Создание контактного лица';
        unset($delete_button);
        $action = '/user/default/createcontact/id/'.$model->id;
    }
?>
<div id="o-edit-contact">
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'contactform',
    'action'=>$action,
    'enableClientValidation'=>true,
    'clientOptions'=>array(
        'validateOnSubmit'=>true,
        'afterValidate'=>'js:function( form, data, hasError ) 
            {     
                if( hasError ){
                    return false;
                }
                else{
                    return true;
                }
            }'
    ),));
?>
<div class="buttons">
    
<?php  
    echo CHtml::button('Закрыть', array('id'=>'close-contact', 'class'=>'btn'));
    echo $delete_button;
   
    if (!$model->id) {
        echo CHtml::submitButton($submit_text,array('id'=>'but_'.$name,'class'=>'btn btn-green')); 
    }
?>
    <h2>
        <?php echo $header_form ?>
    </h2>
    <?php 
    if ($mess = Yii::app()->user->getFlash('error')){
        echo '<div class="message success">'.$mess.'</div>';
    }
   ?>
</div>
<div class="surname field">
<?php  echo $form->error($model, 'surname'); 
    echo $form->labelEx($model, 'surname');
    echo $form->textField($model, 'surname', array('disabled' => ($model->id)?true:false)); ?>
</div>
<div class="name field">
<?php  echo $form->error($model, 'name'); 
    echo $form->labelEx($model, 'name');
    echo $form->textField($model, 'name', array('disabled' => ($model->id)?true:false));?>
</div>
<?php if(!$model->id): ?>
<div class="secondname field">
<?php  echo $form->error($model, 'secondname'); 
    echo $form->labelEx($model, 'secondname');
    echo $form->textField($model, 'secondname'); ?>
</div>
<?php endif ?>
<div class="email field">
<?php  echo $form->error($model, 'email');
    echo $form->labelEx($model, 'email');
    echo $form->emailField($model, 'email', array('disabled' => ($model->id)?true:false)); ?>
</div>
<div class="phone field">
<?php  echo $form->error($model, 'phone');
    echo $form->labelEx($model, 'phone');
    echo $form->textField($model, 'phone', array('disabled' => ($model->id)?true:false)); ?>
</div>
<?php if(!$model->id): ?>
<div class="phone2 field">
<?php  echo $form->error($model, 'phone2');
    echo $form->labelEx($model, 'phone2');
    echo $form->textField($model, 'phone2'); ?>
</div>
<?php endif ?>
<?php if ($model->id): ?>
<div style="display:none;">
<?php  echo $form->hiddenField($model, 'password'); ?>
</div>
<?php endif; ?>
<?php
    $this->endWidget();
?> 
</div>

<script>
    $(document).ready(function(){
        $('#close-contact').click(function(){document.location.href = "<?php echo Yii::app()->getBaseUrl(true) ?>/user/option/";});
    });
</script>
