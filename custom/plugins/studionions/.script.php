<?php
ob_start();
?>
<script type="text/javascript">	
/*
	function showParameters(ctrl)
	{
		var c, p, df, cp;
		var ar, label, value, key, dt, defaultVal, tr;
		currentParams = {}; // reset;
		if (ctrl && ctrl.form) {
			f = ctrl.form;
		} else {
			f = document.forms['mutate'];
			if (!f) {
				return;
			}
		}
		tr = document.getElementById('displayparamrow');
		// check if codemirror is used
		var props = typeof myCodeMirrors != 'undefined' && typeof myCodeMirrors['properties'] != 'undefined' ? myCodeMirrors['properties'].getValue() : f.properties.value, t, td, dp, desc;
		// convert old schemed setup parameters
		if (!IsJsonString(props)) {
			dp = props ? props.match(/([^&=]+)=(.*?)(?=&[^&=]+=|$)/g) : ''; // match &paramname=
			if (!dp) {
				tr.style.display = 'none';
			} else {
				for (p = 0; p < dp.length; p++) {
					dp[p] = (dp[p] + '').replace(/^\s|\s$/, ''); // trim
					ar = dp[p].match(/(?:[^\=]|==)+/g); // split by =, not by ==
					key = ar[0];		// param
					ar = (ar[1] + '').split(';');
					label = ar[0];	// label
					dt = ar[1];		// data type
					value = decode((ar[2]) ? ar[2] : '');

					// convert values to new json-format
					if (key && (dt === 'menu' || dt === 'list' || dt === 'list-multi' || dt === 'checkbox' || dt === 'radio')) {
						defaultVal = decode((ar[4]) ? ar[4] : ar[3]);
						desc = decode((ar[5]) ? ar[5] : '');
						currentParams[key] = [];
						currentParams[key][0] = {'label': label, 'type': dt, 'value': ar[3], 'options': value, 'default': defaultVal, 'desc': desc};
					} else if (key) {
						defaultVal = decode((ar[3]) ? ar[3] : ar[2]);
						desc = decode((ar[4]) ? ar[4] : '');
						currentParams[key] = [];
						currentParams[key][0] = {'label': label, 'type': dt, 'value': value, 'default': defaultVal, 'desc': desc};
					}
				}
			}
		} else {
			currentParams = JSON.parse(props);
		}
		t = '<table width="100%" class="displayparams grid"><thead><tr><td><?= $_lang['parameter'] ?></td><td><?= $_lang['value'] ?></td><td style="text-align:right;white-space:nowrap"><?= $_lang["set_default"] ?> </td></tr></thead>';
		try {
			var type, options, found, info, sd;
			var ll, ls, sets = [], lv, arrValue, split;
			for (var key in currentParams) {
				if (key === 'internal' || currentParams[key][0]['label'] == undefined) {
					return;
				}
				cp = currentParams[key][0];
				type = cp['type'];
				value = cp['value'];
				defaultVal = cp['default'];
				label = cp['label'] != undefined ? cp['label'] : key;
				desc = cp['desc'] + '';
				options = cp['options'] != undefined ? cp['options'] : '';
				ll = [];
				ls = [];
				if (options.indexOf('==') > -1) {
					// option-format: label==value||label==value
					sets = options.split('||');
					for (i = 0; i < sets.length; i++) {
						split = sets[i].split('==');
						ll[i] = split[0];
						ls[i] = split[1] != undefined ? split[1] : split[0];
					}
				} else {
					// option-format: value,value
					ls = options.split(',');
					ll = ls;
				}
				switch (type) {
					case 'int':
					case 'number':
						c = '<input type="number" name="prop_' + key + '" value="' + value + '" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)" />';
						break;
					case 'color':
					case 'email':
					case 'tel':
					case 'date':
					case 'datetime':
					case 'time':
						c = '<input type="' + type + '" name="prop_' + key + '" value="' + value + '" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)" />';
						break;
					case 'menu':
						c = '<select name="prop_' + key + '" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)">';
						if (currentParams[key] === options) {
							currentParams[key] = ls[0];
						} // use first list item as default
						for (i = 0; i < ls.length; i++) {
							c += '<option value="' + ls[i] + '"' + ((ls[i] === value) ? ' selected="selected"' : '') + '>' + ll[i] + '</option>';
						}
						c += '</select>';
						break;
					case 'list':
						if (currentParams[key] === options) {
							currentParams[key] = ls[0];
						} // use first list item as default
						c = '<select name="prop_' + key + '" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)">';
						for (i = 0; i < ls.length; i++) {
							c += '<option value="' + ls[i] + '"' + ((ls[i] === value) ? ' selected="selected"' : '') + '>' + ll[i] + '</option>';
						}
						c += '</select>';
						break;
					case 'list-multi':
						// value = typeof ar[3] !== 'undefined' ? (ar[3] + '').replace(/^\s|\s$/, "") : '';
						arrValue = value.split(',');
						if (currentParams[key] === options) {
							currentParams[key] = ls[0];
						} // use first list item as default
						c = '<select name="prop_' + key + '" size="' + ls.length + '" multiple="multiple" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)">';
						for (i = 0; i < ls.length; i++) {
							if (arrValue.length) {
								found = false;
								for (j = 0; j < arrValue.length; j++) {
									if (ls[i] === arrValue[j]) {
										found = true;
									}
								}
								if (found === true) {
									c += '<option value="' + ls[i] + '" selected="selected">' + ll[i] + '</option>';
								} else {
									c += '<option value="' + ls[i] + '">' + ll[i] + '</option>';
								}
							} else {
								c += '<option value="' + ls[i] + '">' + ll[i] + '</option>';
							}
						}
						c += '</select>';
						break;
					case 'checkbox':
						lv = (value + '').split(',');
						c = '';
						for (i = 0; i < ls.length; i++) {
							c += '<label><input type="checkbox" name="prop_' + key + '[]" value="' + ls[i] + '"' + ((contains(lv, ls[i]) === true) ? ' checked="checked"' : '') + ' onchange="setParameter(\'' + key + '\',\'' + type + '\',this)" /> ' + ll[i] + '</label>&nbsp;';
						}
						break;
					case 'radio':
						c = '';
						for (i = 0; i < ls.length; i++) {
							c += '<label><input type="radio" name="prop_' + key + '" value="' + ls[i] + '"' + ((ls[i] === value) ? ' checked="checked"' : '') + ' onchange="setParameter(\'' + key + '\',\'' + type + '\',this)" /> ' + ll[i] + '</label>&nbsp;';
						}
						break;
					case 'textarea':
						c = '<textarea name="prop_' + key + '" rows="4" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)">' + value + '</textarea>';
						break;
					default:  // string
						c = '<input type="text" name="prop_' + key + '" value="' + value + '" onchange="setParameter(\'' + key + '\',\'' + type + '\',this)" />';
						break;
				}
				info = '';
				info += desc ? '<br/><small>' + desc + '</small>' : '';
				sd = defaultVal != undefined ? '<a title="<?= $_lang["set_default"] ?>" href="javascript:;" class="btn btn-primary" onclick="setDefaultParam(\'' + key + '\',1);return false;"><i class="fa fa-refresh"></i></a>' : '';
				t += '<tr><td class="labelCell" width="20%"><span class="paramLabel">' + label + '</span><span class="paramDesc">' + info + '</span></td><td class="inputCell relative" width="74%">' + c + '</td><td style="text-align: center">' + sd + '</td></tr>';
			}
			t += '</table>';
			createAssignEventsButton();
		} catch (e) {
			t = e + '\n\n' + props;
		}
		td = document.getElementById('displayparams');
		td.innerHTML = t;
		tr.style.display = '';
		if(JSON.stringify(currentParams) === '{}')return;
		implodeParameters();
	}
	*/
</script>
<?php
$script = ob_get_contents();
ob_end_clean();
?>