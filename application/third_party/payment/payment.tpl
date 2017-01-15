<ul class="nav3"> 
<li><a href="{$_A.query_url}/list" id="c_so">{$MsgInfo.payment_name_list}</a></li> 
<li><a href="{$_A.query_url}/all">{$MsgInfo.payment_name_all}</a></li> 
</ul> 

{if $_A.query_type == "new" || $_A.query_type == "edit" || $_A.query_type == "start" }
<div class="module_add">
<form name="form1" method="post" action=""  enctype="multipart/form-data">
	<div class="module_title"><strong>{ if $_A.query_type == "edit" }{$MsgInfo.payment_name_edit}{else}{$MsgInfo.payment_name_new}{/if}</strong></div>
	
	
	<div class="module_border">
		<div class="w">{$MsgInfo.payment_name_name}£∫</div>
		<div class="c">
			<input type="text" name="name"  class="input_border" value="{ $_A.payment_result.name}" size="30" />
		</div>
	</div>
	
	<div class="module_border" >
		<div class="w">{$MsgInfo.payment_name_litpic}£∫</div>
		<div class="c">
			<input type="file" name="litpic" size="30" class="input_border"/>{if $_A.payment_result.litpic!=""}<a href="./{ $_A.payment_result.litpic}" target="_blank" title="”–Õº∆¨"><img src="{ $tpldir }/images/ico_1.jpg" border="0"  /></a><input type="checkbox" name="clearlitpic" value="1" />»•µÙÀı¬‘Õº{/if}</div>
	</div>
	
	{foreach from="$_A.payment_result.fields" item="item" }
	<div class="module_border">
		<div class="w">{$item.label}</div>
		<div class="c">
			{if $item.type=="string"}
			<input type="text" name="config[{$key}]"  class="input_border" value="{ $item.value}" size="30" />
			{elseif $item.type=="select"}
			<select name="config[{$key}]">
				{foreach from="$item.options" key="_key" item="var"}
				<option value="{$_key}" {if $item.value==$_key} selected="selected"{/if}>{$var}</option>
				{/foreach}
			</select>
			{/if}
		</div>
	</div>
	{/foreach}
	
	
	<div class="module_border">
		<div class="w">{$MsgInfo.payment_name_order}:</div>
		<div class="c">
			<input type="text" name="order"  class="input_border" value="{ $_A.payment_result.order|default:10}" size="10" />
		</div>
	</div>

	
	<div class="module_border">
		<div class="w">{$MsgInfo.payment_name_description}£∫</div>
		<div class="c">
			<textarea id="bcontents" name="description" rows="28"  style="width: 100%">{$_A.payment_result.description}</textarea>		
	
		{literal}<script>
		$('#bcontents').xheditor({skin:'o2007blue',upImgUrl:"/?admin&q=plugins&p=xheditor&immediate=1",upImgExt:"jpg,jpeg,gif,png"});
		function submitForm(){
		$("#bcontents").val();
		$('#frm').submit();
		return false;}
		</script>
		{/literal}
		</div>
	</div>
	
	<div class="module_submit" >
		<input type="hidden" name="nid" value="{ $_A.payment_result.nid }" />
		<input type="hidden" name="status" value="{ $_A.payment_result.status|default:1 }" />
		<input type="hidden" name="type" value="{ $_A.payment_result.type }" />
		{if $_A.query_type == "edit"}
		<input type="hidden" name="id" value="{ $magic.request.id }" />
		{/if}
		<input type="submit"  name="submit" value="{$MsgInfo.payment_name_submit}" />
		<input type="reset"  name="reset" value="{$MsgInfo.payment_name_reset}" />
	</div>
	
</div>
</form>
{literal}
<script>
function change(type){
	if (type==1){
		$("#fee").hide();
		$("#fee_money").show();
	}else{
		$("#fee_money").hide();
		$("#fee").show();
	}

}
function check_form(){
/*
	 var frm = document.forms['form1'];
	 var title = frm.elements['name'].value;
	 var errorMsg = '';
	  if (title.length == 0 ) {
		errorMsg += '±ÍÃ‚±ÿ–ÎÃÓ–¥' + '\n';
	  }
	  if (errorMsg.length > 0){
		alert(errorMsg); return false;
	  } else{  
		return true;
	  }
	  */
}

</script>
{/literal}

{elseif $_A.query_type == "all" }

<div class="module_add">
	<div class="module_title"><strong>{$MsgInfo.payment_name_all}</strong></div>
</div>
<table  border="0"  cellspacing="1" bgcolor="#CCCCCC" width="100%">
	<form action="{$_A.query_url}/action" method="post">
	<tr >
		<td width="*" class="main_td">{$MsgInfo.payment_name_logo}</td>
		<td width="*" class="main_td">{$MsgInfo.payment_name_name}</td>
		<td width="*" class="main_td">{$MsgInfo.payment_name_description}</td>
		<td width="" class="main_td">{$MsgInfo.payment_name_manage}</td>
	</tr>
	{ foreach  from=$_A.payment_list key=key item=item}
		<tr class="tr1">
		<td><img src="{ $item.logo}" height="50" /></td>
		<td>{$item.name}</td>
		<td>{$item.description}</td>
		<td>{if $item.type==1}<a href="{$_A.query_url}/start&nid={$item.nid}" >{$MsgInfo.payment_name_open}</a>{else}<a href="{$_A.query_url}/new&nid={$item.nid}" >{$MsgInfo.payment_name_new}</a>{/if}</td>
		</tr>
		{ /foreach}
		
	</form>	
</table>

{elseif $_A.query_type == "list" }
<div class="module_add">
	<div class="module_title"><strong>{$MsgInfo.payment_name_list}</strong></div>
</div>
<table  border="0"  cellspacing="1" bgcolor="#CCCCCC" width="100%">
	<form action="{$_A.query_url}/action" method="post">
	<tr >
		<td width="*" class="main_td">{$MsgInfo.payment_name_logo}</td>
		<td width="*" class="main_td">{$MsgInfo.payment_name_name}</td>
		<td width="*" class="main_td">{$MsgInfo.payment_name_description}</td>
		<td width="" class="main_td">{$MsgInfo.payment_name_manage}</td>
	</tr>
	{ foreach  from=$_A.payment_list key=key item=item}
		<tr class="tr1">
		<td><img src="{ $item.logo}" height="50" /></td>
		<td>{$item.name}</td>
		<td>{$item.description}</td>
		<td><a href="{$_A.query_url}/edit&nid={$item.nid}&id={$item.id}" >{$MsgInfo.payment_name_edit}</a> |  <a href="#" onClick="javascript:if(confirm('{$MsgInfo.payment_name_del_msg}')) location.href='{$_A.query_url}/del&id={$item.id}'">{$MsgInfo.payment_name_del}</a> | {if $item.status==1}<a href="{$_A.query_url}/list&nid={$item.nid}&id={$item.id}&status=0" >{$MsgInfo.payment_name_close}</a>{else}<a href="{$_A.query_url}/list&nid={$item.nid}&id={$item.id}&status=1" >{$MsgInfo.payment_name_open}</a>{/if} </td>
		</tr>
		{ /foreach}
		
	</form>
</table>
{/if}