<script src="/wp-content/plugins/ip-logger-tracker/js/vue.global.prod.js"></script>
<script>
document.addEventListener("DOMContentLoaded",function() {    
    var app = new Vue({
    el: "#app",
    data() {
      return {
        title:"<?php print TITLE; ?>",
        rows:[],
        filter_date:"",
        filter_ip:"",
        filter_path:"",
        filter_location_data:"",
      };
    },
    mounted()
    {
       this.getData(); 
    },
    methods:{
      async getData()
       {
            let filters={
                          filter_date:this.filter_date,
                          filter_ip:this.filter_ip,
                          filter_path:this.filter_path,
                          filter_location_data:this.filter_location_data,
                        }
            let response = await fetch('<?php print $api_link ?>&filters='+JSON.stringify(filters));
            let data = await response.json();
            
            this.rows=data.rows;
      },
      jsonDecode(json)
      {
          try
          {
            var json=JSON.parse(json);

            if(json.status=="success")
            {
              return json.country+","+json.regionName+","+json.city+","+json.isp;
            }
          }
          catch(e)
          {
            console.log(e);
          }
      }
    }
});
});
</script>
<div id="app">
  <div class="wrap">
    <h1>{{title}}</h1>
    <table class="wp-list-table widefat fixed striped table-view-list wrap">
        <thead>
          <th>Date</th>
          <th>IP</th>
          <th>Path</th>
          <th>IP Info</th>
        </thead>
        <tr class="filters">
            <td>
              <input type="text" name="date" v-model="filter_date" @keyup="getData()">
            </td>
            <td>
              <input type="text" name="ip"  v-model="filter_ip" @keyup="getData()">
            </td>
            <td>
              <input type="text" name="path"  v-model="filter_path" @keyup="getData()">
            </td>
            <td>
              <input type="text" name="location" v-model="filter_location_data" @keyup="getData()">
            </td>
        </tr>
        <tr v-for="row in rows">
            <td>
              {{ row.date }}
            </td>
            <td>
              {{ row.ip }}
            </td>
            <td>
              {{ row.path }}
            </td>
            <td>
              {{ jsonDecode(row.location_data) }}
            </td>
        </tr>
        <tr v-if="rows.length==0" style="text-align:center;">
          <td colspan="4">No results</td>
        </tr>
    </table>
  </div>
</div>