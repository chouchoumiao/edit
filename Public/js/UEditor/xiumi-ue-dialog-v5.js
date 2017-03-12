/**
 * Created by shunchen_yang on 16/10/25.
 */
UE.registerUI('dialog', function (editor, uiName) {
	var btn = new UE.ui.Button({
		name   : 'xiumi-connect',
		title  : '秀米',
		onclick: function () {
			var dialog = new UE.ui.Dialog({
				iframeUrl: PUBLIC+'/js/UEditor/xiumi-ue-dialog-v5.html',
				editor   : editor,
				name     : 'xiumi-connect',
				title    : "秀米图文消息助手",
				cssRules : "width: " + (window.innerWidth - 100) + "px;" + "height: " + (window.innerHeight - 100) + "px;",
			});
			dialog.render();
			dialog.open();
		}
	});

	return btn;
});