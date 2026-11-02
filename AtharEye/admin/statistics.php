<?php
require_once __DIR__.'/../includes/helpers.php';
require_role('admin');
$title=t('reports.title');
$s=stats();
$q=trim($_GET['q']??''); $risk=$_GET['risk']??''; $from=$_GET['from']??'';
$where="WHERE fa.status='approved'"; $params=[];
if($q!==''){ $where.=' AND (s.name LIKE ? OR u.name LIKE ? OR fa.risk_type LIKE ?)'; $like="%$q%"; array_push($params,$like,$like,$like); }
if(in_array($risk,['منخفض','متوسط','مرتفع'],true)){ $where.=' AND fa.risk_level=?'; $params[]=$risk; }
if($from!==''){ $where.=' AND fa.visit_date>=?'; $params[]=$from; }
$rows=fetch_all("SELECT fa.*,s.name site_name,s.city,u.name archaeologist FROM ae_field_assessments fa JOIN ae_heritage_sites s ON s.id=fa.site_id JOIN ae_users u ON u.id=fa.archaeologist_id $where ORDER BY fa.visit_date DESC,fa.id DESC",$params);
if(($_GET['export']??'')==='csv'){
  header('Content-Type:text/csv; charset=utf-8'); header('Content-Disposition:attachment; filename=athareye-reports-'.date('Y-m-d').'.csv');
  echo "\xEF\xBB\xBF"; $out=fopen('php://output','w'); fputcsv($out,[t('reports.site'),t('reports.archaeologist'),t('reports.risk'),t('common.date'),t('reports.recommendations')]);
  foreach($rows as $r) fputcsv($out,[$r['site_name'],$r['archaeologist'],risk_label($r['risk_level']).' '.$r['risk_percentage'].'%',$r['visit_date'],$r['recommendations']]);
  fclose($out); exit;
}
$cityRows=fetch_all('SELECT city,COUNT(*) total FROM ae_heritage_sites GROUP BY city ORDER BY total DESC,city');
$maxCity=max(1,...array_map(fn($r)=>(int)$r['total'],$cityRows));
$total=max(1,$s['sites']); $highDeg=round(($s['high']/$total)*100,2); $mediumDeg=round((($s['high']+$s['medium'])/$total)*100,2);
include __DIR__.'/../includes/header.php';
?>
<section class="page-hero"><span class="eyebrow"><?= h(t('reports.label')) ?></span><h1><?= h(t('reports.title')) ?></h1><p><?= h(t('reports.desc')) ?></p></section>
<div class="cards">
  <?php $cards=[[$s['sites'],'reports.sites','ti-map-pin'],[$s['high'],'reports.high','ti-alert-triangle'],[$s['approved_assessments'],'reports.approved','ti-certificate'],[$s['pending_assessments'],'reports.pending','ti-clock-hour-4']]; foreach($cards as $c): ?>
  <div class="card stat-line"><div><div class="stat-num"><?= (int)$c[0] ?></div><b><?= h(t($c[1])) ?></b></div><div class="icon-box"><i class="ti <?= h($c[2]) ?>"></i></div></div>
  <?php endforeach; ?>
</div>
<div class="analytics-grid">
  <section class="analytics-card"><h3><?= h(t('reports.risk_distribution')) ?></h3><div class="donut-wrap"><div class="risk-donut" style="--high:<?= $highDeg ?>%;--medium:<?= $mediumDeg ?>%"><div class="risk-donut-center"><div><b><?= $s['sites'] ?></b><small style="display:block;color:var(--muted)"><?= h(t('reports.sites')) ?></small></div></div></div><div class="chart-legend"><span><i style="background:var(--danger)"></i><?= h(t('risk.high')) ?>: <b><?= $s['high'] ?></b></span><span><i style="background:var(--warning)"></i><?= h(t('risk.medium')) ?>: <b><?= $s['medium'] ?></b></span><span><i style="background:var(--success)"></i><?= h(t('risk.low')) ?>: <b><?= $s['low'] ?></b></span></div></div></section>
  <section class="analytics-card"><h3><?= h(t('reports.city_distribution')) ?></h3><div class="bar-chart"><?php foreach($cityRows as $r): $width=max(4,round(((int)$r['total']/$maxCity)*100)); ?><div class="bar-row"><span><?= h($r['city']) ?></span><div class="bar-track"><div class="bar-fill" style="width:<?= $width ?>%"></div></div><b><?= (int)$r['total'] ?></b></div><?php endforeach; ?></div></section>
</div>
<section class="table-card">
  <div class="section-heading compact"><span class="eyebrow"><?= h(t('reports.latest')) ?></span><h2><?= h(t('reports.latest')) ?></h2></div>
  <form class="reports-toolbar" method="get"><input class="form-control" name="q" value="<?= h($q) ?>" placeholder="<?= h(t('reports.search')) ?>"><select class="form-select" name="risk"><option value=""><?= h(t('common.all')) ?> — <?= h(t('reports.risk')) ?></option><?php foreach(['منخفض','متوسط','مرتفع'] as $level): ?><option value="<?= h($level) ?>" <?= $risk===$level?'selected':'' ?>><?= h(risk_label($level)) ?></option><?php endforeach; ?></select><input class="form-control" type="date" name="from" value="<?= h($from) ?>"><button class="btn btn-primary"><i class="ti ti-filter"></i><?= h(t('common.filter')) ?></button></form>
  <div class="actions" style="margin-bottom:15px"><a class="btn btn-light" href="admin/statistics.php?<?= h(http_build_query(array_merge($_GET,['export'=>'csv']))) ?>"><i class="ti ti-file-export"></i><?= h(t('common.export')) ?></a></div>
  <div class="table-responsive"><table class="table"><thead><tr><th><?= h(t('reports.report')) ?></th><th><?= h(t('reports.site')) ?></th><th><?= h(t('reports.archaeologist')) ?></th><th><?= h(t('reports.risk')) ?></th><th><?= h(t('common.date')) ?></th><th><?= h(t('reports.recommendations')) ?></th><th></th></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td class="report-title-cell"><b><?= h(localized('تقرير '.$r['site_name'],'Assessment: '.$r['site_name'])) ?></b><br><span class="mini"><?= h(excerpt($r['notes'],100)) ?></span></td><td><?= h($r['site_name']) ?><br><span class="mini"><?= h($r['city']) ?></span></td><td><?= h($r['archaeologist']) ?></td><td><span class="badge badge-<?= h(risk_class($r['risk_percentage'])) ?>"><?= h(risk_label($r['risk_level'])) ?> — <?= (int)$r['risk_percentage'] ?>%</span></td><td><?= h($r['visit_date']) ?></td><td><?= h(excerpt($r['recommendations'],120)) ?></td><td class="table-actions"><a class="btn btn-light" href="site.php?id=<?= (int)$r['site_id'] ?>"><i class="ti ti-external-link"></i><?= h(t('reports.open_site')) ?></a></td></tr><?php endforeach; ?><?php if(!$rows): ?><tr><td colspan="7" class="empty"><?= h(t('reports.no_data')) ?></td></tr><?php endif; ?></tbody></table></div>
</section>
<?php include __DIR__.'/../includes/footer.php'; ?>
