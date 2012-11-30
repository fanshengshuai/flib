{include 'header.tpl'}
<table width="960" border="0" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td><img src="images/banner2.jpg" width="960" height="140" /></td>
    </tr>
    <tr>
        <td style="padding-top:2px"><table width="960" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td width="180" valign="top" style="background:url(images/left_bg.gif) #f7f7f7 left top no-repeat">
                        {include "news/left.tpl"}
                    </td>
                    <td width="25">&nbsp;</td>
                    <td width="755" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr>
                                <td height="32"><a href="home.asp" class="l3">首页</a> &gt; <a href="/news" class="l3">新闻</a> </td>
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
                                            <td width="740" align="left"><span class="t5">新闻中心</span></td>
                                        </tr>
                                </table></td>
                            </tr>
                            <tr>
                                <td height="22">&nbsp;</td>
                            </tr>
                            <tr>
                                <td style="line-height:180%;color:#727272;padding-bottom:80px;">
                                    <table width="715" border="0" align="center" cellpadding="0" cellspacing="0">
                                        {foreach from=$news_list['data'] item=item}
                                        <tr>
                                            <td height="30">
                                                &middot;<a href="/news/{$item['news_id']}.html" title="{$item['title']}" class="l3">{$item['title']}</a>
                                            </td>
                                        </tr>
                                        {/foreach}
                                        <tr>
                                            <td height="30">
                                                {$news_list['page_option']['html']}
                                            </td>
                                        </tr>
                                    </table>

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
{include 'footer.tpl'}
