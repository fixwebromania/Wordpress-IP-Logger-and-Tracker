<link rel="stylesheet" href="<?php print get_site_url(); ?>/wp-content/plugins/ip-logger-tracker/css/style.css">
<script src="<?php print get_site_url(); ?>/wp-content/plugins/ip-logger-tracker/js/vue.global.prod.js"></script>
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
        loading:false,
        advanced_filter:false,
        exclude_my_ip:true,
        exclude_ip:""
      };
    },
    mounted()
    {
       this.getData(); 
    },
    methods:{
      async getData()
       {
            this.loading=true;

            let filters={
                          filter_date:this.filter_date,
                          filter_ip:this.filter_ip,
                          filter_path:this.filter_path,
                          filter_location_data:this.filter_location_data,
                          exclude_my_ip:this.exclude_my_ip,
                          exclude_ip:this.exclude_ip
                        }
            let response = await fetch('<?php print $api_link ?>&filters='+JSON.stringify(filters));
            let data = await response.json();
            
            this.rows=data.rows;

            this.loading=false;
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
    <div id="loading" v-if="loading">
      <div id="loading-spinner"></div>
    </div>
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
              <a class="button button-primary" style="float:right"  @click="advanced_filter ? advanced_filter=false : advanced_filter=true" href="#">
                  Advanced Filter
               </a>
            </td>
        </tr>
        <tr class="filters" v-if="advanced_filter">
            <td>
              <label>Exclude My IP</label>
              <input type="checkbox" name="exclude_my_ip"  v-model="exclude_my_ip" @change="getData()">
            </td>
            <td>
              <label>Exclude IP</label></div>
              <input type="text" name="exclude_ip"  v-model="exclude_ip" @keyup="getData()">
            </td>
            <td>
             
            </td>
            <td>
              
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