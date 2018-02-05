<hr/>
<footer id="piedDePage">
<p><a href="index.php"><?php echo gettext('Accueil');?></a> | <a href="<?php echo CORPUS_WEB;?>"><?php echo gettext('Données');?></a>
<?php if (!empty($footerMenu)) {echo $footerMenu;}?></p>
<p class="copyright">Copyright © Mathieu Mangeot, GETALP, <?php echo gettext('tous droits réservés');?></p>
</footer>
</body>
</html>
