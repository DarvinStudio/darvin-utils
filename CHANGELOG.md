6.5.0: New transliterator.

6.5.4: Add "inverse" option to new object flag annotation.

6.5.6: Add importable interface.

6.6.0: Add "format_price" Twig filter.

6.6.1: Add strict transliterator.

7.0.0: 

- remove redundant Composer script handler;

- remove redundant exception classes;

- add tree sorter interface;

- remove "_" from transliterator's allowed symbol list;

- rename events;

- object namer: remove "Interface" suffix.

7.0.2: Add TemplateMailerInterface::sendEmail().

7.0.3: Support nullable booleans in stringifier.

7.0.4: Add StringsUtil::truncate().

7.0.5:
 
- Move triplebox form type from Admin bundle.

- Refactor stringifying booleans.

7.0.6: Catch Swift Mailer exceptions in mailer.

7.0.7: Add compress response event subscriber.

7.1.0: Move mailer to Mailer bundle.

7.1.1: 

- Load collections in custom entity loader.

- Enable strict type in custom object functionality classes.

7.1.3: Annotate phone validation constraint class with "@Annotation".

7.1.4: Cloner: call "__clone()" method of object's copy.

7.1.5: Cloner: call methods specified in "callAfter" property of "Clonable" annotation instead of "__clone()".

7.1.9: Add iterable utility.

7.2.0: Enable strict types.

7.2.1: Use "object" type hint.

7.2.2: Rename stringifier interface to Doctrine stringifier.

7.2.3: Add generic stringifier.

7.2.5: Make user query builder filterer optional in new entity counter.

7.2.6: Add "class" and "interface" params to config loader.

7.2.7: Add callback runner.

7.2.8: Implement multiple mode in entity to ID form data transformer.

7.2.9: EntityManager => EntityManagerInterface.

7.3.0:

- Add override command.

- Move price formatter from utils bundle.

- Move macros template from utils bundle.

- Move translations from utils bundle.

7.3.2:

- Rename "Sluggable entity manager" => "Sluggable manager".

- Replace custom sluggable exception with generic invalid argument exception.

7.3.4: Remove redundant HomepageRouterInterface::getHomepageRoute().

7.3.5: Add antispam form theme.

7.3.7: Added template for price on request

7.3.8: Catch exception in Cloner::setValue().

7.3.9: Add copy cloned uploadables event subscriber.

7.3.10: Add "thousands_separator" option to price formatter.

7.3.11: Add JSON encoder.

7.3.14: Fix checking parent Doctrine metadata existence in extended metadata factory.

7.3.15: Trim stringifier result.

7.3.17: Add HTTP cache clear command.

7.3.18: Remove integration with yandex translate API.

7.3.19: Add Varnish cache clear command.

7.3.22: Add QueryBuilderUtil::findOrCreateJoin().

7.3.25: Add file size converter.

7.3.28: Add LocaleProviderInterface::getDefaultLocale().

7.3.30: Add plain_data() macro.

7.3.32: Render links in property macros.

7.3.33: Remove some checks from annotation drivers to support mapped superclasses.

7.3.34: Data macros: support translation IDs in data keys.

7.4.0: Add data view services.

7.4.2: Extension configurator: allow multiple configs for same extension.
