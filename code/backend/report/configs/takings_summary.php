<?php
$dataset="Takings Summary";

#SQL including table joins (LEAVE TRAILING SPACE)
$sql=<<<EOF
sales.dte "Date", sales.gross_sales "Gross Sales" 
, round(sales.vat,2) "VAT" 
, (sales.gross_sales - round(sales.vat,2)) "Net Sales" , if (cash2.cash_value>0,cash2.cash_value, 0) "Cash" , if (abs(cash.petty)>0,cash.petty*-1,0) "Petty Cash" 
, if(abs(cash2.cash_value - cash.petty)>0,(cash2.cash_value - cash.petty),0) "Float" 
, if(abs(tilldrawer.startval + cash2.cash_value + cash.petty - tilldrawer.closeval)>0,(tilldrawer.startval + cash2.cash_value + cash.petty - tilldrawer.closeval),0) "Banked" 
, if (card.streamline>0,card.streamline,0) "Credit Cards"
, if (amex.amex>0,amex.amex,0) "Amex"
, if (website.website>0,website.website,0) "Website"
, if (GVs.GVissue>0,GVs.GVissue,0) "GV Issued"
, if (GVr.GVissue>0,GVr.GVissue,0) "GV Redeemed"  
, if (CNs.CNissue>0,CNs.CNissue,0) "CN Issued"
, if (CNr.CNissue>0,CNr.CNissue,0) "CN Redeemed"  		  		  
from (select date_format(od.timestamp, '%d/%m/%Y') dte 
, sum(if(od.zero_price=1,0,(if (abs(od.actualgrand)>0, od.actualgrand,od.grandTot)*abs(od.qty)))) gross_sales 
, sum(if(od.zero_price=1,0,(if (abs(od.actualvat)>0, od.actualvat,od.vatTot)*abs(od.qty)))) vat 
, sum(if(od.zero_price=1,0,(if (abs(od.actualnet)>0, od.actualnet,od.netTot)*abs(od.qty)))) net_sales 
from orderdetail od where 1=1 and od.status not in ('A','X','P','N') 
and od.timestamp [[DDATE]] group by 1 ) sales 
left join (select date_format(timestamp, '%d/%m/%Y') dte,sum(transamnt) petty 
	from pettycash where timestamp [[DDATE]] group by 1 ) cash on sales.dte=cash.dte 
left join (select date_format(oh.transDate, '%d/%m/%Y') dte, max(td.startval) startval,max(td.closeval) closeval 
from tilldrawer td, orderheader oh where 1=1 and oh.till_session = td.tillsession 
and oh.transDate [[DDATE]] group by 1) tilldrawer on sales.dte = tilldrawer.dte 
left join (select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) cash_value 
from tenders t where 1=1 and t.PayMethod in (1) and t.transDate [[DDATE]] 
group by 1 ) cash2 on sales.dte = cash2.dte
left join (select dte, sum(streamline) streamline from 
(select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) streamline 
from tenders t where 1=1 and t.PayMethod in (3,4,7) and t.transDate [[DDATE]] 
group by 1 
union
select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) streamline 
from spendpottenders t where 1=1 and t.PayMethod in (3,4,7) and t.transDate [[DDATE]] 
group by 1) un group by 1 ) card on sales.dte = card.dte
left join (select dte, sum(amex) amex from 
(select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) amex 
from tenders t where 1=1 and t.PayMethod =6 and t.transDate [[DDATE]] 
group by 1 
union
select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) amex 
from spendpottenders t where 1=1 and t.PayMethod =6 and t.transDate [[DDATE]] 
group by 1) un group by 1  ) amex on sales.dte = amex.dte 
left join (select dte, sum(website) website from 
(select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) website 
from tenders t where 1=1 and t.PayMethod = 5 and t.transDate [[DDATE]] 
group by 1 
union
select date_format(t.transDate, '%d/%m/%Y') dte, sum(PayValue) website 
from spendpottenders t where 1=1 and t.PayMethod = 5 and t.transDate [[DDATE]] 
group by 1) un group by 1  ) website on sales.dte = website.dte
left join (select date_format(s.createdDate, '%d/%m/%Y') dte, sum(s.amount) GVissue
from spendpots s where 1=1 and s.type = 'G' and s.createdDate [[DDATE]]
group by 1 ) GVs on sales.dte = GVs.dte 
left join (select date_format(s.usedDate, '%d/%m/%Y') dte, sum(s.amount) GVissue
from spendpots s where 1=1 and s.type = 'G' and s.usedDate [[DDATE]]
group by 1 ) GVr on sales.dte = GVr.dte
left join (select date_format(s.createdDate, '%d/%m/%Y') dte, sum(s.amount) CNissue
from spendpots s where 1=1 and s.type = 'C' and s.createdDate [[DDATE]]
group by 1 ) CNs on sales.dte = CNs.dte 
left join (select date_format(s.usedDate, '%d/%m/%Y') dte, sum(s.amount) CNissue
from spendpots s where 1=1 and s.type = 'C' and s.usedDate [[DDATE]]
group by 1 ) CNr on sales.dte = CNr.dte
left join (select date_format(ts.session_date, '%d/%m/%Y') dte, max(td.closeval) float_close
from tilldrawer td, till_sessions ts
where 1=1
and td.till = ts.till
and ts.session_date [[DDATE]]
and td.tillsession = td.tillsession
group by dte
) td on sales.dte = td.dte 
order by 1 asc
EOF;

#getSelect key for select code
$filters[0]=array();

#Predicate equivalent
$filters[1]=array();

#Group by name
$filters[2]=array();

#Pre-select names
$filters[3]=array();

#Row titles
$filters[4]=array();

#What is the date field called?
$date="t.timestamp";

#can we group by everything (to be implemented)
$group_by=array(1,1,1,1);

#Orientation
$orient="landscape";
$debug=0;
$category="Financial";
?>
