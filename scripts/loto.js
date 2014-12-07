$(function() {
    $( ".startGame" ).click(function () {
        $(".modal").addClass("hide");
        Game.Connect();
    })
  });
  
Interface = {};

Interface.DraggableCard = function ()
{
    var startPosition = $( "#draggable" ).offset();
    $( "#draggable" ).draggable({
		drag: function( event, ui ) {
			$(".block").each(function( index ) {
				var position = $( this ).offset();
				if (Math.abs(ui.position.left-position.left)<150
					&& Math.abs(ui.position.top-position.top)<75
				) {
					$(this).addClass('active');
				} else {
					$(this).removeClass('active');
				}
			});
			if ($(".block.active").length) {
				$( "#draggable" ).offset($(".block.active").offset());
				$( "#draggable .title" ).text($(".block.active i").text());
				Game.Choice($(".block.active").attr("data-placeid"));
			} else {
				$( "#draggable" ).offset(startPosition);
				$( "#draggable .title" ).text("");
			}
		},
		stop: function( event, ui ) {
			if ($(".block.active").length) {
				$( "#draggable" ).offset($(".block.active").offset());
				$( "#draggable .title" ).text($(".block.active i").text());
			} else {
				$( "#draggable" ).offset(startPosition);
				$( "#draggable .title" ).text("");
			}
		}
	});
}