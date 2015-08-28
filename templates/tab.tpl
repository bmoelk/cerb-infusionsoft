<script type="text/javascript" src="/resource/bmoelk.infusionsoft/js/jquery.tokeninput.js"></script>
<link rel="stylesheet" type="text/css" href="/resource/bmoelk.infusionsoft/css/token-input.css" />

<script type="text/javascript" src="/resource/bmoelk.infusionsoft/js/jquery.notifyBar.js"></script>
<link rel="stylesheet" type="text/css" href="/resource/bmoelk.infusionsoft/css/jquery.notifyBar.css" />

<fieldset>
  <legend>Customer Information</legend>
  <div class="property">
    <b>InfusionSoft Id:</b>
    <span style="font-weight:bold;color:rgb(50,115,185);">{$contact->Id}</span>
  </div>
  <div class="property">
    <b>InfusionSoft Username:</b>
    <span style="font-weight:bold;color:rgb(50,115,185);">{$contact->Username}</span>
  </div>
  <div class="property">
    <b>First Name:</b>
    <span style="font-weight:bold;color:rgb(50,115,185);">{$contact->FirstName}</span>
  </div>
  <div class="property">
    <b>Last Name:</b>
    <span style="font-weight:bold;color:rgb(50,115,185);">{$contact->LastName}</span>
  </div>
  <div class="property">
    <b>Phone:</b>
    <span>{$contact->Phone1} {$contact->Phone1Ext} [{$contact->Phone1Type}]</span>
  </div>
  <div class="property">
    <b>Email:</b>
    <span style="font-weight:bold;color:rgb(50,115,185);">{$contact->Email}</span>
  </div>
  <div class="property">
    <b>Created By:</b>
    <span>{$contact->CreatedBy}</span>
  </div>
  <div class="property">
    <b>Date Created:</b>
    <span>{$contact->DateCreated}</span>
  </div>
  <div class="property">
    <b>Date Last Updated:</b>
    <span>{$contact->LastUpdated}</span>
  </div>
  <div class="property">
    <b>Contact Notes:</b>
    <span>{$contact->ContactNotes}</span>
  </div>

  <br clear="all">

</fieldset>

<fieldset>
  <legend>Billing Address</legend>
  <div class="property">
    <span>{$contact->StreetAddress1}</span>
  </div>
  <div class="property">
    <span>{$contact->City}</span>, <span>{$contact->State}</span> <span>{$contact->PostalCode}</span>
  </div>
  <div class="property">
    <span>{$contact->Country}</span>
  </div>
</fieldset>
<fieldset>
  <legend>Shipping Address</legend>
  <div class="property">
    <span>{$contact->Address2Street1}</span>
  </div>
  <div class="property">
    <span>{$contact->City2}</span>, <span>{$contact->State2}</span> <span>{$contact->PostalCode2}</span>
  </div>
  <div class="property">
    <span>{$contact->Country2}</span>
  </div>
</fieldset>
<fieldset>
  <legend>Social Networks</legend>
</fieldset>
<fieldset>
  <legend>Web Portal Credentials</legend>
</fieldset>
<fieldset>
  <legend>Tags</legend>

    <input type="text" id="infusionsoft-contact-tags" name="tags" />
    <div id="infusionsoft-contact-tags-status"></div>
    <input id="infusionsoft-contact-tags-save" type="button" value="Save Changes" style="margin-top:25px"/>
    <script type="text/javascript">
      $(document).ready(function() {ldelim}
        var infusionsoftBaseUrl = "{devblocks_url}{/devblocks_url}ajax.php?context_id={$context_id}&c=infusionsoft&_csrf_token={$csrf_token}";

        $("#infusionsoft-contact-tags").tokenInput(infusionsoftBaseUrl + '&a=find_tag', {ldelim}
          method: "GET",
          queryParam: "tag",
          hintText: "Type in a tag name",
          preventDuplicates: true,
          prePopulate: [
          {foreach $groups as $group}
            {ldelim}id: {$group['Id']}, name: "{$group['GroupName']}"{rdelim},
          {/foreach}
          ],
          onDelete: function(tag){ldelim}
            console.log(tag);
            infusionsoftTagDeltas.push("R" + tag['id']);
          {rdelim},
          onAdd: function(tag){ldelim}
            console.log(tag);
            infusionsoftTagDeltas.push("A" + tag['id']);
          {rdelim}
        {rdelim});

        var infusionsoftTagDeltas = [];
        jQuery("#infusionsoft-contact-tags-save").click(function(){ldelim}
          console.log(infusionsoftTagDeltas);
          jQuery.ajax(infusionsoftBaseUrl + '&a=update_tags&tag_deltas=' + infusionsoftTagDeltas.join(',') + '&contact_id={$contact->Id}', {ldelim}
            cache: false,
            method: 'GET',
            success: function(data, status, xhr){ldelim}
              console.log('tag deltas processed successfully:' + status);
              jQuery.notifyBar({ cssClass: "success", html: "Tags saved!" });
              console.log(data);
              infusionsoftTagDeltas = [];

              jQuery('#infusionsoft-contact-tags-status').text("success!");

            {rdelim},
            error: function(xhr, status, error){ldelim}
              console.log('tag deltas error:' + status);
              console.log(error);
            {rdelim}
          {rdelim});
        {rdelim});


      {rdelim});
    </script>

</fieldset>




<fieldset>
  <legend>Payment History</legend>
  <span>TODO</span>
</fieldset>
<fieldset>
  <legend>Subscriptions</legend>
  <span>TODO</span>
</fieldset>
<fieldset>
  <legend>Referral Partner Details</legend>
  <span>TODO</span>
</fieldset>
<fieldset>
  <legend>Coaching Questionaire</legend>
  <span>TODO</span>
</fieldset>



{include file="devblocks:cerberusweb.core::internal/views/search_and_view.tpl" view=$view}


