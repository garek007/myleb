<?php
  foreach($fields as $fieldname => $field){
    $ftype = $field[0];

    $plc = $field[2];
    $required;//need to check value of required and add class to input if it is
    switch($ftype){
      case "text":
      case "textarea":
      case "url":
      case "select":
      case "image":
        ${$fieldname} = makeField($fieldname,$field,$ftype);
        break;
      case "freetext":
        ${$fieldname} = '<p>'.$field[1].'</p';
        break;
      default: echo "not working";break;

    }
  }
function makeField($fname,$f,$ftype){
  $field='<label for="'.$fname.'">'.$f[1].'</label>';
  if(!empty($f[2])){
    $p = 'placeholder="'.$f[2].'"';
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
    case "image":
      $field .='<input '.$name_id.' type="text"';
      if(!empty($f[2])){
        $field.= 'class="'.$f[2].'"';
      }
      $field.='data-width="'.$f[3].'" data-height="'.$f[4].'" ';
      break;
    default:break;
  }
  $field.='>';
  return $field;
}
