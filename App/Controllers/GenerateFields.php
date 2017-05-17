<?php

class GenerateFields{
  public function processFields($f3)
  {



    $info = $f3->get('PARAMS');
    $template = str_replace('/','',$info[0]);

		//$json = file_get_contents($_SERVER['DOCUMENT_ROOT'] ."/settings/templates.json");
    $json = $f3->read('settings/templates.json');
	  $templates = json_decode($json);
	  $fields = $templates->$template->fields;
    $fieldsArray = array();

    foreach($fields as $fieldname => $field){
      $ftype = $field[0];

      $plc = $field[2];
      $required;//need to check value of required and add class to input if it is
      switch($ftype){
        case "text":
        case "textarea":
        case "url":
        case "select":
        case "drop":
        case "radio":
          $fieldsArray[$fieldname] = makeField($fieldname,$field,$ftype);
          break;
        case "freetext":
          if($field[2]){
            $fieldsArray[$fieldname] = '<'.$field[2].'>'.$field[1].'</'.$field[2].'>';
          }else{
            $fieldsArray[$fieldname] = '<p>'.$field[1].'</p>';
          }
          break;
        default: echo "ERROR: Field type not setup in GenerateFields.php";break;
      }
    }

    $f3->set('fields',$fieldsArray);
    $f3->set('content',$template.'/'.$template.'-frm.htm');
		echo Template::instance()->render('base.htm');
    

  }
}//end of class



function makeField($fname,$f,$ftype){
$field='<label for="'.$fname.'">'.$f[1].'</label>';
if(!empty($f[2])){
  $p = 'placeholder="'.$f[2].'"';
  $p .= 'value="'.$f[2].'"';
}
if($f[3]=="required"){
  $r = "required";
}
$name_id = 'name="'.$fname.'" id="'.$fname.'"';
switch($ftype){
  case "text":
    $field .= '<input '.$name_id.' type="text" '.$p.' '.$r;
    break;
  case "textarea":
    $field .= '<textarea '.$name_id.' type="text" '.$p.'></textarea';
    break;
  case "url":
    $field .= '<input '.$name_id.' type="url" '.$p;
    break;
  case "select":
    $field .='<select '.$name_id.' type="text" >';
    $field .='<option value="custom">Select Option</option>';
    foreach($f[2] as $option){
      $field.= '<option value="'.$option.'" id="'.$option.'">'.$option.'</option>';
    }
    $field.='</select';
    break;
  case "drop":

    $field .='<input '.$name_id.' type="text"';
    if(!empty($f[2])){
      $field.= 'class="'.$f[2].'"';
    }
    $field.='data-width="'.$f[3].'" data-height="'.$f[4].'" ';
    $field.='data-type="'.$f[5].'" ';
    break;
  case "radio":
    $radio = true;
    foreach($f[4] as $option){
      $field .= '<div class="input-wrapper"><input name="'.$fname.'" type="radio" value="'.$option.'">'.$option.'</div>';
    }

    break;
  default:break;
}
if($radio == false){ $field.='>'; }


return $field;

}
