<br />
<form name="myform" action="submit.php" method="post">
  <table width="50%" border="0" align="center">
    <tr>
      <th align="center">SQL to CSV</th>
    </tr>
    <tr>
      <td align="center">
        <input type="text" name="sql" value="" size="50" maxlength="256" /></td>
    </tr>
    <tr>
      <td align="center">
       <input type="hidden" name="page" value="export_to_csv" />
       <input type="hidden" name="cmd" value="export_to_csv"/ >
       <input type="submit" value="Generate CSV">
      </td>
    </tr>
  </table>
</form>
