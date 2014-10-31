<?php

$json = file_get_contents('api.json');
$object = json_decode($json);

function println($str = '',$count = 0) {
	$tabs = '';
	for ($i = 0; $i < $count; $i++) {
		$tabs = "\t";
	}
	echo $tabs.$str."\n";
}
function printthis($str = '',$count = 0) {
	println("this.".$str,$count);
}
function printcomment($str = '',$count = 0) {
	println(' * '.$str,$count);
}
function isupper($str) {
	return strtoupper($str) == $str;
}
function printend() {
	println('};');
	println();
}

//Get a list of the classes that can be instantiated so we can handle these as functions not singletons
$classes = array();
foreach($object as $module => $data) {
	$methods = $data->methods;
	foreach($methods as $method) {
		$name = $method->name;
		if (strpos($name,'create') === 0) {
			$classes[substr($name,strlen('create'))] = 'BLANK';
		}
	}
}

function isclass($lastelement) {
	global $classes;
	/*
	$isclass = false;
	foreach ($classes as $class) {
		if ($class == $lastelement) {
			$isclass = true;
			break;
		}
	}
	return $isclass;
	*/
	return $classes[$lastelement] != '';
}

function converttype($type) {
	$return = $type;
	if ($type == null) {
		$return = '';
	} else if ($type == 'void') {
		$return = '';
	} else if ($type == 'string') {
		$return = 'String';
	} else if ($type == 'float') {
		$return = 'Float';
	} else if ($type == 'double') {
		$return = 'Double';
	} else if ($type == 'int') {
		$return = 'Integer';
	} else if ($type == 'object') {
		$return = 'Object';
	} else if ($type == 'function') {
		$return = 'Function';
	} else if ($type == 'number') {
		$return = 'Number';
	} else if ($type == 'boolean') {
		$return = 'Boolean';
	} else if ($type == 'date') {
		$return = 'Date';
	} else if ($type == 'array') {
		$return = 'Array'; //Not sure if this is a type in ScriptDoc
	} else if (strpos($type,'object') !== false) {
		$return = 'Object';
	} else if (strpos($type,'array') !== false) {
		$return = 'Array';
	} else if (strpos($type,'string') !== false) {
		$return = 'String';
	}
	return $return;
}

function converttypes($types) {
	$types = explode(',',$types);
	$cleantypes = '';
	foreach ($types as $type) {
		if ($cleantypes == '') {
			$cleantypes = converttype($type);
		} else {
			$cleantypes = $cleantypes.','.converttype($type);
		}
	}
	return $cleantypes;
}

function getreturnvalue($type, $nullblank = false) {
	$type = converttype($type);
	if ($type == '') {
		if ($nullblank) {
			return '';
		} else {
			return 'null';
		}
	} else if (strpos($type,'Number') === 0) {
		return 'new Number()';
	} else if (strpos($type,'Float') === 0) {
		return '0.0';
	} else if (strpos($type,'Double') === 0) {
		return '0.0';
	} else if (strpos($type,'Integer') === 0) {
		return '0';
	} else if (strpos($type,'Boolean') === 0) {
		return 'false';
	} else if (strpos($type,'String') === 0) {
		return "''";
	} else if (strpos($type,'Array') === 0) {
		return '[]';
	} else if (strpos($type,'Object') === 0) {
		return '{}';
	} else if (strpos($type,'Date') === 0) {
		return 'new Date()';
	} else {
		return '{}'; //Default to object?
	}
}

function cleanparam($param) {
	if ($param == 'default') {
		return 'def';
	} else {
		return $param;
	}
}

function printheader($data) {
	println('/**');
	printcomment(trim(strip_tags($data->description)));
	if ($data->deprecated != null) {
		printcomment('@deprecated '.$data->deprecated);
	}
	printcomment('@since '.$data->since);
	println(' */');
}

function printmethodheader($name,$desc,$return = '',$parameters = null,$namespace = null,$count = 0) {
	println('/**',$count);
	printcomment(trim(strip_tags($desc)),$count);
	if ($parameters != null) {
		foreach ($parameters as $parameter) {
			$param = cleanparam($parameter->name);
			if (strpos($parameter->description,'optional') === 0 || strpos($parameter->description,'(optional)') === 0) {
				$descparam = '['.$param.']';
			} else {
				$descparam = $param;
			}
			printcomment('@param {'.converttypes($parameter->type).'} '.$descparam.' '.$parameter->description,2);
		}
	}
	printcomment('@alias '.$namespace.'.'.$name,$count);
	if ($return != '') {
		printcomment('@return {'.$return.'}',$count);
	}
	println(' */',$count);
}

function printproperties($properties,$namespace,$upper = true,$lower = true,$json = false) {
	foreach ($properties as $property) {
		$name = $property->name;
		$isupper = isupper($name);
		if (($isupper && $upper) || (!$isupper && $lower)) {
			$type = converttypes($property->type);
			$returnvalue = getreturnvalue($type);
			println('/**',2);
			printcomment(trim(strip_tags($property->value)),2);
			printcomment('@alias '.$namespace.'.'.$name,2);
			printcomment('@property {'.$type.'}',2);
			println(' */',2);
			if ($json) {
				println($name.': '.$returnvalue.',',1);
			} else {
				printthis($name.' = '.$returnvalue,1);
			}
			if (!$isupper) {
				$uname = ucfirst($name);
				$getname = 'get'.$uname;
				$setname = 'set'.$uname;
				
				$desc = trim(strip_tags($property->value));
				printmethodheader($getname,'Get the '.$desc,$type,null,$namespace,2);
				if ($json) {
					println($getname.': function () { return '.$name.'; },',1);
				} else {
					printthis($getname.' = function() { return this.'.$name.'; }',1);
				}
				$params = array();
				unset($param);
				$param->type = $property->type;
				$param->description = 'New value to set.';
				$param->name = 'value';
				$params[] = $param;
				printmethodheader($setname,'Set the '.$desc,'',$params,$namespace,2);
				if ($json) {
					println($setname.': function () { },',1);
				} else {
					printthis($setname.' = function(value) { this.'.$name.' = value; }',1);
				}
			}
		}
	}
}

function printmethods($methods,$namespace,$json = false) {
	foreach ($methods as $method) {
		global $classes;
		$name = $method->name;
		//Titanium.UI.PickerColumn.removeRow[object]
		//Titanium.UI.PickerColumn.addRow[object]
		if (strpos($name,'[object]') !== false) {
			$name = substr($name, 0, strlen($name) - strlen('[object]'));
		}
		$iscreate = strpos($name,'create') === 0;
		if ($iscreate) {
			$return = $classes[substr($name,strlen('create'))];
		} else {
			$return = converttype($method->returntype);
		}
		printmethodheader($name,$method->value,$return,$method->parameters,$namespace,2);
		$params = '';
		$i = 0;
		$usednames = array();
		foreach ($method->parameters as $parameter) {
			$param = cleanparam($parameter->name);
			//Some of the documentation has duplicate variable names, eg. Titanium.App.fireEvent
			$arraycount = array_count_values($usednames);
			if ($arraycount[$param] != null) {
				$param = $param . ($arraycount[$param]+1);
			}
			if ($params == '') {
				$params = $param;
			} else {
				$params .= ','.$param;
			}
			$usednames[] = $param;
			$i++;
			//extra variables in Titanium.App.Properties.setInt
			if ($name == 'setInt' && $i >= 2) {
				break;
			}
		}
		if ($iscreate) {
			$returntext = 'return new '.$return.'(); ';
		} else {
			$returnvalue = getreturnvalue($return,true);
			if ($returnvalue != '') {
				$returntext = 'return '.$returnvalue.'; ';
			} else {
				$returntext = '';
			}
		}
		if ($json) {
			println($name.': function ('.$params.') { '.$returntext.'},',2);
		} else {
			printthis($name.' = function '.$name.'('.$params.') { '.$returntext.'}',2);
		}
	}
}

//Create dummy functions that will be used in create functions
foreach($object as $module => $data) {
	$modulearray = explode('.',$module);
	$lastelement = $modulearray[count($modulearray)-1];
	$isclass = isclass($lastelement);
	if ($isclass) {
		printheader($data);
		$functionname = '';
		foreach ($modulearray as $element) {
			$functionname .= ($functionname == '' ? '' : '_').$element;
		}
		$classes[$lastelement] = $functionname;
		println('function '.$functionname.'() {');
		printproperties($data->properties,$functionname,false,true); //Don't add constants for classes
		printmethods($data->methods,$functionname);
		printend();
	}
}

foreach($object as $module => $data) {
	$modulearray = explode('.',$module);
	$lastelement = $modulearray[count($modulearray)-1];
	$isclass = isclass($lastelement);
	//2DMatrix & 3DMatrix functions cause js parse errors
	if (preg_match('/^[0-9]+.*/',$lastelement) == 0) {
		printheader($data);
		println(''.$module.' = {');

		if (isset($_REQUEST['lite'])) {
			printproperties($data->properties,$module,true,!$isclass,true); //Only print uppercase properties for class
			if (!$isclass) { printmethods($data->methods,$module,true); } //Don't print methods for class, as these are in the dummy functions
		} else {
			printproperties($data->properties,$module,true,true,true);
			printmethods($data->methods,$module,true);
		}
		printend();
	}
}

println('/**');
printcomment('Get the current active window');
printcomment('@alias Titanium.UI.currentWindow');
printcomment('@return {Titanium_UI_Window}');
println(' */');
println('Titanium.UI.currentWindow = new Titanium_UI_Window();');
println('/**');
printcomment('Get the current active tab');
printcomment('@alias Titanium.UI.currentTab');
printcomment('@return {Titanium_UI_Tab}');
println(' */');
println('Titanium.UI.currentTab = new Titanium_UI_Tab();');

?>