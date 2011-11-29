function numberval(valor){
	if(valor.length>0){
		return parseFloat(valor);
	}else{
		return 0;
	}
}

function nformat(num,n){
	var i=0;
	var fact=1;
	miles='<?php echo $miles; ?>';
	centimos='<?php echo $centimos; ?>';
	num = num.toString().replace(/$|\,/g,'');
	if(isNaN(num)) num = "0";
	for(i=0;i < n;i++){ fact=10*fact; }
	sign  = (num == (num = Math.abs(num)));
	num   = Math.floor(num*fact+0.50000000001);
	//alert(num);
	cents = num%fact;
	num   = Math.floor(num/fact).toString();
	if(cents<10) cents = "0" + cents;
		for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
			num = num.substring(0,num.length-(4*i+3))+miles+ num.substring(num.length-(4*i+3));
	return (((sign)?'':'-') + num + centimos + cents);
}

function des_nformat(num){
	miles='<?php echo $miles; ?>';
	centimos='<?php echo $centimos; ?>';
	num = num.split(miles).join('');
	num = parseFloat(num.replace(centimos,'.'));
	if(isNaN(num)) return(0);
	return(num);
}

function moneyformat(num){
	return nformat(num,2);
}

function des_moneyformat(num){
	return des_nformat(num);
}

function roundNumber(rnum,rlength){
	return Math.round(rnum*Math.pow(10,rlength))/Math.pow(10,rlength);
}

var STR_PAD_LEFT = 1;
var STR_PAD_RIGHT = 2;
var STR_PAD_BOTH = 3;
 
function pad(str, len, pad, dir) {
	if (typeof(len) == "undefined") { var len = 0; }
	if (typeof(pad) == "undefined") { var pad = ' '; }
	if (typeof(dir) == "undefined") { var dir = STR_PAD_RIGHT; }
	if (len + 1 >= str.length) {
		switch (dir){
			case STR_PAD_LEFT:
				str = Array(len + 1 - str.length).join(pad) + str;
			break;
			case STR_PAD_BOTH:
				var right = Math.ceil((padlen = len - str.length) / 2);
				var left = padlen - right;
				str = Array(left+1).join(pad) + str + Array(right+1).join(pad);
			break;
			default:
				str = str + Array(len + 1 - str.length).join(pad);
			break;
		} // switch
	}
	return str; 
}