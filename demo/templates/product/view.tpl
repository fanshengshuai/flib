{include "header.tpl"}
<table width="960" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td><img src="/images/banner4.jpg" width="960" height="140" /></td>
    </tr>
    <tr>
        <td style="padding-top:2px"><table width="960" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="180" valign="top" style="background:url(/images/left_bg.gif) #f7f7f7 left top no-repeat"><table width="170" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td height="90" valign="top"><table width="170" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td height="18">&nbsp;</td>
                                        </tr>
                                        <tr>
                                            <td height="26" align="right" class="t3">Exhibition Hall</td>
                                        </tr>
                                        <tr>
                                            <td height="26" align="right" class="t4">{$cate_info['c_name']}</td>
                                        </tr>
                                </table></td>
                            </tr>
                            <tr>
                                <td>
                                    <ul class="sub">
                                        {foreach from=$cate_list item=item}
                                        <li><a style="{if $item['cid'] eq $sub_pid}color:red;{/if}" href="/products/{$item['cid']}">{$item['c_name']}</a></li>
                                        {/foreach}
                                    </ul>
                                </td>
                            </tr>
                    </table></td>
                    <td width="25">&nbsp;</td>
                    <td width="755" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td height="32"><a href="/" class="l3">首页</a> &gt; {$p_info['title']}</td>
                            </tr>
                            <tr>
                                <td height="1" bgcolor="#E6E6E6"></td>
                            </tr>
                            <tr>
                                <td height="22">&nbsp;</td>
                            </tr>
                            <tr>
                                <td><table width="755" border="0" cellspacing="0" cellpadding="0">
                                        <tr>
                                            <td width="5" height="14" bgcolor="#E61F1A"></td>
                                            <td width="10" align="left"></td>
                                            <td width="740" align="left"><span class="t5">{$p_info['title']}</span></td>
                                        </tr>
                                </table></td>
                            </tr>
                            <tr>
                                <td height="22">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="line-height:180%;color:#727272;padding-bottom:80px;">
                                    {$p_info['content']}
                                </td>
                            </tr>
                    </table></td>
                </tr>
        </table></td>
    </tr>
    <tr>
        <td height="2"></td>
    </tr>
</table>
