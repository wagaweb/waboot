jQuery(document).ready(function($){
  $('a.img-colorbox').colorbox({
	  maxWidth:"90%",
	  maxHeight:"90%",
	  rel: true,
	  close: "",
	  previous:"",
	  next:"",
	  current:"image {current} of {total}"
  });
});