(function( $ ) {
	/* Additional code for multipliers form. */
	(function () {
		var $adderBox, $select, $btn, armRemoveBtn;
		$adderBox = $( '.js-currency-adder' );
		if ( $adderBox.length ) {
			$select = $adderBox.find( 'select' );
			$btn = $adderBox.find( 'input' );
			armRemoveBtn = function ( $btn ) {
				$btn.on( 'click', function () {
					$btn.parents( '.js-p24-multiplier-box' ).remove();
				});
				return $btn;
			};
			$( '.js-p24-multiplier-box input[type=button]' ).each( function ( idx, elm ) {
				armRemoveBtn( $( elm ) );
			});
			$btn.on( 'click', function () {
				var $lastInputBox, name, selectVal, $newInputBox, removeBtn;
				/* Use last input as template and insert a new one. */
				$lastInputBox = $( '.js-p24-multiplier-box' ).last();
				name = $lastInputBox.find( 'input[type=number]' ).attr( 'name' );
				selectVal = $select.val();
				name = name.replace( /\[[^\]]*\]$/, '[' + selectVal + ']' );
				$newInputBox = $lastInputBox.clone();
				$newInputBox
					.find( 'input[type=number]' )
						.attr( 'name', name )
						.val( 1 )
						.prop( 'disabled', false );
				removeBtn = $newInputBox.find( 'input[type=button]' );
				removeBtn.css( 'display', 'unset' );
				armRemoveBtn( removeBtn ) ;
				$newInputBox.find( 'label' ).text( selectVal );
				$lastInputBox.after( $newInputBox );
			});
		}
	})();

})( jQuery );
