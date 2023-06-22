{OVERALL_HEADER}
<div id="titlebar">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h2>{LANG_QUOTEVIO}</h2>
                <!-- Breadcrumbs -->
                <nav id="breadcrumbs">
                    <ul>
                        <li><a href="{LINK_INDEX}">{LANG_HOME}</a></li>
                        <li>{LANG_QUOTEVIO}</li>
                    </ul>
                </nav>

            </div>
        </div>
    </div>
</div>
<div class="container margin-bottom-50">
    <div class="row"><!-- user-login -->
        <div class="col-xl-8 margin-0-auto">
            <div class="user-account clearfix">
                <h2 class="margin-bottom-50">{LANG_QUOTEVIO}</h2>
                <form action="#" method="post">
                    <input type="hidden" name="sender_id" value="{SENDER_ID}">
                    <div class="submit-field">
                      <h5>Amount</h5>
                      <input class="with-border" type="text" name="amount" value="{AMOUNT}">
                      IF("{AMOUNT_ERROR}"!=""){ <span style="color: red">{AMOUNT_ERROR}</span>{:IF}
                    </div>
                    <div class="submit-field">
                      <h5>Message</h5>
                      <textarea class="with-border" name="message">{MESSAGE}</textarea>
                      IF("{MESSAGE_ERROR}"!=""){ <span style="color: red">{MESSAGE_ERROR}</span>{:IF}
                    </div>
                    <button type="submit" name="Submit" id="submit" class="button">{LANG_QUOTEVIO}</button>
                </form>
                <!-- checkbox -->
            </div>
        </div>
        <!-- user-login -->
    </div>
    <!-- row -->
</div>
{OVERALL_FOOTER}
