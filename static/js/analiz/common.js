var cash_analiz_com_lbl = {
    id: "cash_analiz_com_lbl",
    xtype: 'label',
    cls: "cash_analiz_lbl",
    text: lang(116)
};


var cash_analiz_com_from_date =
{
    xtype: 'datefield',
    //startDay:1,
    fieldLabel: lang(43),
    name: 'cash_analiz_com_from_date',
    id: 'cash_analiz_com_from_date',
    labelWidth: 55,
    format: settings.date_format,
    maxValue: new Date(),
    width: 160,
    onChange: cash_analiz_com_refresh
}; // cash_analiz_com_from_date


var cash_analiz_com_to_date =
{
    xtype: 'datefield',
    fieldLabel: lang(44),
    //startDay:1,
    name: 'cash_analiz_com_to_date',
    id: 'cash_analiz_com_to_date',
    labelWidth: 20,
    format: settings.date_format,
    width: 120,
    onChange: cash_analiz_com_refresh
}; // cash_analiz_com_to_date


var cash_analiz_com_date = {
      xtype: 'toolbar',
      dock: 'top',
      ui: 'footer',
      items: [cash_analiz_com_from_date, " ", cash_analiz_com_to_date],
      region: 'north',
      id: "cash_analiz_com_date"
}; //cash_analiz_com_date


function cash_analiz_com_refresh() {
  if(Ext.getCmp('cash_analiz_com_from_date').getValue() == null) return;
  if(Ext.getCmp('cash_analiz_com_to_date').getValue() == null) return;

  cash_analiz_com_store.proxy.url = "ajax/analiz/common.php?from=" + Ext.Date.format(Ext.getCmp('cash_analiz_com_from_date').getValue(),'Y-m-d') +
				    "&to=" + Ext.Date.format(Ext.getCmp('cash_analiz_com_to_date').getValue(),'Y-m-d') + getUsrFltr();
  cash_analiz_com_store.load();

  setAnkhor();
} //cash_analiz_com_refresh

var cash_analiz_com_model = Ext.define('cash_analiz_com_model', {
    extend: 'Ext.data.Model',
    fields: [
      {name: 'tname',		type: 'string'},
      {name: 'data', 		type: 'double'}
    ]
}); //cash_analiz_com_model

var cash_analiz_com_store = Ext.create('Ext.data.Store', {
    model: 'cash_analiz_com_model',
    autoLoad: false,
    proxy: {
      type: 'ajax',
      url: 'ajax/analiz/common.php?'
    }
}); //cash_analiz_com_store

var cash_analiz_com_chart = Ext.create('Ext.chart.Chart', {
    id: "cash_analiz_com_chart",
    width: w - 100,
    height: h - 100,
    animate: true,
    store: cash_analiz_com_store,
    shadow: true,
    legend: {
      position: 'right'
    },
    insetPadding: 60,
    theme: 'Base:gradients',
    series: [{
      type: 'pie',
      field: 'data',
      showInLegend: true,
      donut: false,
      colorSet: ['#a61120', '#94ae0a'],
      tips: {
        trackMouse: true,
        width: 220,
        height: 28,
        renderer: function(storeItem, item) {
          var total = 0;
          cash_analiz_com_store.each(function(rec) {
            total += rec.get('data');
          });
          this.setTitle(storeItem.get('tname') + ' ('+price_r(storeItem.get('data'))+'): ' + Math.round(storeItem.get('data') / total * 100) + '%');
        }
      },
      highlight: {
        segment: {
          margin: 20
        }
      },
      label: {
          field: 'tname',
          display: 'rotate',
          contrast: true,
          font: '18px Arial',
          renderer: function (value, label, storeItem) { return value + ' ('+price_r(storeItem.get('data'))+')'; }
      }
    }]
});


function cash_analiz_com_load(_cb) {
  if(Ext.getCmp('cash_analiz_com').items.length > 0) {
    if(_cb != undefined) _cb();
    return;
  }

  Ext.getCmp('cash_analiz_com').add(cash_analiz_com_lbl);
  Ext.getCmp('cash_analiz_com').add(cash_analiz_com_date);
  Ext.getCmp('cash_analiz_com').add(cash_analiz_com_chart);

  if(isDefaultAnaliz() || Ext.getCmp('cash_analiz_com_from_date').getValue() == null ) {
    var cd = new Date();

    Ext.getCmp('cash_analiz_com_from_date').setValue( new Date(cd.getFullYear(), cd.getMonth(), 1) );
    Ext.getCmp('cash_analiz_com_to_date').setValue( cd );
  } else {
    setAnalitAnkhorParam();
  }

  if(_cb != undefined) _cb();
}