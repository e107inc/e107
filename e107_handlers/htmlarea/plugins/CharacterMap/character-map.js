// Character Map plugin for HTMLArea
// Sponsored by http://www.systemconcept.de
// Implementation by Holger Hees based on HTMLArea XTD 1.5 (http://mosforge.net/projects/htmlarea3xtd/)
// Original Author - Bernhard Pfeifer novocaine@gmx.net 
//
// (c) systemconcept.de 2004
// Distributed under the same terms as HTMLArea itself.
// This notice MUST stay intact for use (see license.txt).

function CharacterMap(editor) {
        this.editor = editor;
	        
        var cfg = editor.config;
	var toolbar = cfg.toolbar;
	var self = this;
	var i18n = CharacterMap.I18N;
        
	cfg.registerButton({
                id       : "insertcharacter",
                tooltip  : i18n["CharacterMapTooltip"],
                image    : editor.imgURL("ed_charmap.gif", "CharacterMap"),
                textMode : false,
                action   : function(editor) {
                                self.buttonPress(editor);
                           }
            })

	var a, i, j, found = false;
	for (i = 0; !found && i < toolbar.length; ++i) {
		a = toolbar[i];
		for (j = 0; j < a.length; ++j) {
			if (a[j] == "inserthorizontalrule") {
				found = true;
				break;
			}
		}
	}
	if (found)
	    a.splice(j, 0, "insertcharacter");
        else{                
            toolbar[1].splice(0, 0, "separator");
	    toolbar[1].splice(0, 0, "insertcharacter");
        }
};

CharacterMap._pluginInfo = {
	name          : "CharacterMap",
	version       : "1.0",
	developer     : "Holger Hees & Bernhard Pfeifer",
	developer_url : "http://www.systemconcept.de/",
	c_owner       : "Holger Hees & Bernhard Pfeifer",
	sponsor       : "System Concept GmbH & Bernhard Pfeifer",
	sponsor_url   : "http://www.systemconcept.de/",
	license       : "htmlArea"
};

CharacterMap.prototype.buttonPress = function(editor) {
    editor._popupDialog( "plugin://CharacterMap/select_character", function( entity ) 
    {
        if ( !entity ) 
        {  
            //user must have pressed Cancel
            return false;
        }
        
        editor.insertHTML( entity );
               
    }, null);
}

