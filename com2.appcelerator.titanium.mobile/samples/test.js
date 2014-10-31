/*
http://www.phpied.com/3-ways-to-define-a-javascript-class/
http://docs.aptana.com/dev/index.php/Adding_support_for_a_new_Ajax_library#Understanding_MANIFEST.MF_and_plugin.xml
http://docs.aptana.com/docs/index.php/Documenting_your_code_using_ScriptDoc
http://docs.aptana.com/docs/index.php/ScriptDoc_comprehensive_tag_reference
https://developer.appcelerator.com/apidoc/mobile
Social Interview http://bit.ly/dlaTuC

Todo:
	update site
	build file to generate plugin and update update site
	add Ti alias
	get examples from json file as well
	hard code xml document return object if possible
	wonder why it doesn't fully work for full auto complete when its a global reference
	wonder why documentation isn't passed when instantiating
JSON:
	Multiple variable names
		Titanium.App.fireEvent
		Titanium.App.Properties.setInt
		Titanium.UI.PickerColumn.addRow
		Titanium.UI.PickerColumn.deleteRow
	Comment Titanium.Map.createView -> Titanium.Map.createMapView
*/

function Apple (type) {
    this.type = type;
    this.color = "red";
    this.getInfo = getAppleInfo;
}

/** 
* Gets the current foo 
* @param {String} fooId	The unique identifier for the foo.
* @return {Apple}	Returns the current foo.
*/
function createApple(type) {
	return new Apple(type);
}

var test = Apple;
var test2 = new test();
test2.color;
var test3 = createApple();
test3.type;

/**
 * @method MyClass
 * @property staticVariable
 * @return {obj}
 */
obj = function() {
    var staticVariable = "This is available to all functions created here"

    function MyClass(){
		//Depending on the class may build all the class here
		this.publicMethod = function(){
		//Do stuff
		}
	}
	this.MyClass = MyClass
	this.staticVariable = staticVariable
}
/**
 * @return {obj}
 */
Ti.UI.createTest = function() {
	return obj;
}
var test4 = Ti.UI.createTest();
me.test = Ti.UI.createTest();
//Ti.UI.createTest().MyClass
//me.test.MyClass

//test4.staticVariable
//test4.MyClass.publicMethod
//test4.my
//test4
/*
Ti.UI = {
	function button() {
		
	}
}
var test4 = new createTiTest();
*/
//test4.
//var test2 = Ti.test
