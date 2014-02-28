<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
    $submit_text = 'Сохранить';
    $name = $model->id;
    $delete_button = CHtml::link('Удалить пользователя', '/admin/user/deleteuser/id/'.$model->id, array('id'=>'del_'.$model->name,'class'=>'btn del', 'onclick'=>'return confirm("Внимание! Пользователь будет безвозвратно удален. Продолжить?")'));
    $header_form = 'Редактирование пользователя '.$model->login;
    $action = '/admin/user/edituser/id/'.$model->id;
    if ($model->isNewRecord){
        $submit_text = 'Подтвердить';
        $name = 'new';
        $header_form = 'Создание нового пользователя';
        $action = '/admin/user/createuser/';
        unset($delete_button);
    }
?>
<div class="form">
<div class="header-form">
    <?php echo $header_form; ?>
</div>
<?php $form = $this->beginWidget('CActiveForm', array('id'=>'form'.$model->id,
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
<?php  echo $delete_button; 
    echo CHtml::button('Закрыть пользователя',array('onclick'=>'$(".total .right").html(" ");','class'=>'btn'));
    echo CHtml::submitButton($submit_text,array('id'=>'but_'.$name,'class'=>'btn btn-green')); ?>
</div>
<div class="password field">
<?php  echo CHtml::label('Пароль', 'User_password');
    echo CHtml::passwordField('User_password', '', array('id'=>'User_password')); ?>
</div>
<div class="login field">
    <?php  
        echo $form->error($model, 'login'); 
        echo $form->labelEx($model, 'login');
        echo $form->textField($model, 'login');
    ?>    
</div>
<div class="company field">
<?php  echo $form->error($model, 'company'); 
    echo $form->labelEx($model, 'company');
    echo $form->textField($model, 'company'); ?>
</div>
<div class="inn field">
<?php  echo $form->error($model, 'inn'); 
    echo $form->labelEx($model, 'inn');
    echo $form->textField($model, 'inn'); ?>
</div>
<div class="country field">
<?php  echo $form->error($model, 'country'); 
    echo $form->labelEx($model, 'country');
    echo $form->textField($model, 'country'); ?>
</div>
<div class="region field">
<?php  echo $form->error($model, 'region'); 
    echo $form->labelEx($model, 'region');
    echo $form->textField($model, 'region'); ?>
</div>
<div class="city field">
<?php  echo $form->error($model, 'city'); 
    echo $form->labelEx($model, 'city');
    echo $form->textField($model, 'city'); ?>
</div>
<div class="district field">
<?php  echo $form->error($model, 'district'); 
    echo $form->labelEx($model, 'district');
    echo $form->textField($model, 'district'); ?>
</div>

<div class="surname field">
<?php  echo $form->error($model, 'surname'); 
    echo $form->labelEx($model, 'surname');
    echo $form->textField($model, 'surname'); ?>
</div>
<div class="name field">
<?php  echo $form->error($model, 'name'); 
    echo $form->labelEx($model, 'name');
    echo $form->textField($model, 'name');?>
</div>
<div class="secondname field">
<?php  echo $form->error($model, 'secondname'); 
    echo $form->labelEx($model, 'secondname');
    echo $form->textField($model, 'secondname'); ?>
</div>
<!--div class="phone field">
<?php  echo $form->error($model, 'phone');
    echo $form->labelEx($model, 'phone');
    echo $form->emailField($model, 'phone'); ?>
</div-->
<div class="email field">
<?php  echo $form->error($model, 'email');
    echo $form->labelEx($model, 'email');
    echo $form->emailField($model, 'email'); ?>
</div>
<?php /*
$noshow = array('id', 'password');
foreach ($model as $itm=>$v)
{
    if(!in_array($itm, $noshow)):
        echo '<div class="'.$itm.' field">';
        echo $form->error($model, $itm); 
        echo $form->labelEx($model, $itm);
        echo $form->textField($model, $itm);
        echo '</div>';
    endif;
}*/
?>

<div style="display:none;">
<?php  echo $form->hiddenField($model, 'password'); ?>
</div>
<?php $this->endWidget();?> 
</div>
